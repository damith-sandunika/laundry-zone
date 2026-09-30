<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PromotionalCampaign;
use App\Services\CommunicationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendCampaignMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaign;

    // Timeout for the job - can take up to 30 mins for 1000s of SMS
    public $timeout = 1800;

    /**
     * Create a new job instance.
     */
    public function __construct(PromotionalCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->campaign->update(['status' => 'processing']);

        try {
            $customers = $this->getTargetCustomers();
            $this->campaign->update(['recipients_count' => $customers->count()]);

            $commService = new CommunicationService();
            $successCount = 0;
            $failedCount = 0;

            // Determine which template to use based on audience and channel
            $templateType = ($this->campaign->audience_type === 'birthday') ? 'birthday_promotion' : 'bulk_promotion';

            $template = null;
            if ($this->campaign->channel === 'sms') {
                $template = \App\Models\SmsTemplate::where('type', $templateType)->where('is_active', true)->first();
            } elseif ($this->campaign->channel === 'email') {
                $template = \App\Models\EmailTemplate::where('type', $templateType)->where('is_active', true)->first();
            } elseif ($this->campaign->channel === 'whatsapp') {
                $template = \App\Models\WhatsappTemplate::where('type', $templateType)->where('is_active', true)->first();
            }

            if (!$template) {
                throw new \Exception("Active template not found for {$templateType} on {$this->campaign->channel} channel.");
            }

            foreach ($customers as $customer) {
                // Determine recipient record
                $recipientId = DB::table('campaign_recipients')->insertGetId([
                    'promotional_campaign_id' => $this->campaign->id,
                    'customer_id' => $customer->id,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                try {
                    // Send message based on channel
                    if ($this->campaign->channel === 'sms' && $customer->phone) {
                        $content = $template->message;
                        $content = str_replace('{customer_name}', $customer->name ?? 'Valued Customer', $content);
                        $content = str_replace('{branch_name}', $this->campaign->branch->name ?? \App\Models\Setting::get('company_name', 'Laundry Zone'), $content);
                        // Optional placeholders could be populated here
                        $content = str_replace('{promo_code}', 'PROMO' . date('mY'), $content);
                        $content = str_replace('{discount}', '10%', $content);
                        $content = str_replace('{expiry_date}', now()->addDays(7)->format('Y-m-d'), $content);

                        $commService->sendDirectSMS($customer->phone, $content);
                    } elseif ($this->campaign->channel === 'email' && $customer->email) {
                        // TODO: Direct Email if needed, currently reusing mock direct or standard
                        // The system usually queues email, we could just log it or dispatch an email job here
                        // ... implementation depends on exact email service
                    } elseif ($this->campaign->channel === 'whatsapp' && $customer->phone) {
                        // ... implementation depends on whatsapp gateway
                    }

                    DB::table('campaign_recipients')
                        ->where('id', $recipientId)
                        ->update(['status' => 'sent']);
                    $successCount++;
                } catch (\Exception $e) {
                    DB::table('campaign_recipients')
                        ->where('id', $recipientId)
                        ->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
                    $failedCount++;
                }
            }

            $this->campaign->update([
                'status' => 'completed',
                'sent_count' => $successCount,
                'failed_count' => $failedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Campaign Job Failed', ['campaign_id' => $this->campaign->id, 'error' => $e->getMessage()]);
            $this->campaign->update(['status' => 'failed']);
        }
    }

    /**
     * Get customers based on audience type and conditions.
     */
    private function getTargetCustomers()
    {
        $query = Customer::query()->where('is_active', true);
        $conditions = json_decode($this->campaign->audience_conditions, true) ?? [];

        switch ($this->campaign->audience_type) {
            case 'all':
                // All active customers
                break;
            case 'branch_specific':
                $query->where('branch_id', $this->campaign->branch_id);
                break;
            case 'inactive':
                // No orders in the last 3 months
                $threeMonthsAgo = now()->subMonths(3);
                $query->whereDoesntHave('orders', function ($q) use ($threeMonthsAgo) {
                    $q->where('created_at', '>=', $threeMonthsAgo);
                });
                break;
            case 'birthday':
                // Birthday is today
                $query->whereMonth('date_of_birth', now()->month)
                    ->whereDay('date_of_birth', now()->day);
                break;
            case 'top_customers':
                // Filter by min_spent or min_orders using subqueries (like in TopCustomersController)
                if (isset($conditions['min_spent']) && $conditions['min_spent'] > 0) {
                    $query->addSelect([
                        'total_spent' => Order::selectRaw('COALESCE(SUM(total), 0)')
                            ->whereColumn('customer_id', 'customers.id')
                            ->whereIn('status', ['delivered', 'done', 'completed'])
                    ])->having('total_spent', '>=', $conditions['min_spent']);
                }

                if (isset($conditions['min_orders']) && $conditions['min_orders'] > 0) {
                    $query->addSelect([
                        'order_count' => Order::selectRaw('COUNT(*)')
                            ->whereColumn('customer_id', 'customers.id')
                            ->whereIn('status', ['delivered', 'done', 'completed'])
                    ])->having('order_count', '>=', $conditions['min_orders']);
                }
                break;
        }

        // Only return customers with valid phone numbers (assuming SMS is primary)
        $query->whereNotNull('phone')->where('phone', '!=', '');

        return $query->get();
    }
}
