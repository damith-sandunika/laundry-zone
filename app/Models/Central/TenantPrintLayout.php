<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPrintLayout extends Model
{
    use HasFactory;

    protected $connection = 'landlord';

    protected $table = 'tenant_print_layouts';

    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'font_family',
        'font_size',
        'primary_color',
        'accent_color',
        'show_logo',
        'show_header',
        'show_branch_info',
        'show_customer_info',
        'show_qr_code',
        'show_barcode',
        'show_notes',
        'show_footer',
        'header_title',
        'header_subtitle',
        'terms_and_conditions',
        'footer_text',
        'custom_fields',
    ];

    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'show_header' => 'boolean',
            'show_branch_info' => 'boolean',
            'show_customer_info' => 'boolean',
            'show_qr_code' => 'boolean',
            'show_barcode' => 'boolean',
            'show_notes' => 'boolean',
            'show_footer' => 'boolean',
            'custom_fields' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create default layout for a tenant and type.
     */
    public static function getForTenant(?int $tenantId, string $type): self
    {
        if (!$tenantId) {
            return static::defaultLayout($type);
        }

        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'type' => $type],
            static::defaultAttributes($type)
        );
    }

    public static function defaultLayout(string $type): self
    {
        $layout = new static(static::defaultAttributes($type));
        $layout->type = $type;
        return $layout;
    }

    public static function defaultAttributes(string $type): array
    {
        if ($type === 'bill') {
            return [
                'name' => 'Default Thermal Bill',
                'font_family' => "'Inter', system-ui, sans-serif",
                'font_size' => '12px',
                'primary_color' => '#000000',
                'accent_color' => '#1d4ed8',
                'show_logo' => true,
                'show_header' => true,
                'show_branch_info' => true,
                'show_customer_info' => true,
                'show_qr_code' => true,
                'show_barcode' => true,
                'show_notes' => true,
                'show_footer' => true,
                'header_title' => 'Laundry Zone',
                'header_subtitle' => 'Main Branch & Express Care',
                'terms_and_conditions' => "Please bring this receipt for collection. Garments not claimed within 30 days will be disposed of.",
                'footer_text' => 'Thank you for choosing Laundry Zone.',
                'custom_fields' => [
                    'logo_size' => '50',
                    'paper_width' => '300px',
                    'company_name_size' => '18px',
                    'branch_phone' => '011-2345678',
                    'branch_address' => 'No 45, High Level Road, Colombo',
                    'badge_text_paid' => '✓ PAID IN FULL',
                    'badge_text_credit' => '⏳ CREDIT',
                    'badge_text_pickup' => '🕒 PAY ON PICKUP',
                    'review_text' => 'If you are happy with our service, please leave us a Google Review!',
                    'software_branding' => 'Software by DSRZone',
                ],
            ];
        }

        if ($type === 'receipt') {
            return [
                'name' => 'Default Job Card Receipt',
                'font_family' => "'Helvetica Neue', Helvetica, Arial, sans-serif",
                'font_size' => '12px',
                'primary_color' => '#000000',
                'accent_color' => '#059669',
                'show_logo' => true,
                'show_header' => true,
                'show_branch_info' => true,
                'show_customer_info' => true,
                'show_qr_code' => true,
                'show_barcode' => false,
                'show_notes' => true,
                'show_footer' => true,
                'header_title' => 'Laundry Zone',
                'header_subtitle' => 'Workshop Job Card',
                'terms_and_conditions' => 'Care instructions will be followed as per garment label.',
                'footer_text' => 'Quality checked by Laundry Zone.',
                'custom_fields' => [
                    'logo_size' => '45',
                    'paper_width' => '300px',
                    'order_no_size' => '20px',
                    'show_customer_phone' => '1',
                    'show_due_date' => '1',
                    'show_totals' => '1',
                    'show_special_notes' => '1',
                    'notes_label' => 'Special Note :',
                    'notes_line_count' => 2,
                    'show_process_list' => '1',
                    'process_items' => [
                        ['name' => 'Collected', 'suffix' => 'Kamal'],
                        ['name' => 'Wash', 'suffix' => '________'],
                        ['name' => 'Iron', 'suffix' => '________'],
                        ['name' => 'Quality check', 'suffix' => '________'],
                        ['name' => 'Final inspection', 'suffix' => '________'],
                    ],
                ],
            ];
        }

        // Invoice PDF (A4)
        return [
            'name' => 'Default Executive Invoice',
            'font_family' => "'Inter', sans-serif",
            'font_size' => '13px',
            'primary_color' => '#0f172a',
            'accent_color' => '#3b82f6',
            'show_logo' => true,
            'show_header' => true,
            'show_branch_info' => true,
            'show_customer_info' => true,
            'show_qr_code' => true,
            'show_barcode' => true,
            'show_notes' => true,
            'show_footer' => true,
            'header_title' => 'Laundry Zone',
            'header_subtitle' => 'Express Laundry & Premium Dry Cleaning',
            'terms_and_conditions' => 'All dry cleaning and laundry services subject to company terms. Payment due upon order collection.',
            'footer_text' => 'Thank you for your business! 🤍',
            'custom_fields' => [
                'logo_size' => '60',
                'company_name_size' => '24px',
                'invoice_title' => 'INVOICE',
                'invoice_title_size' => '28px',
                'contact_email' => 'contact@company.com',
                'contact_phone' => '+94 11 234 5678',
                'contact_address' => 'Colombo, Sri Lanka',
                'badge_text_paid' => 'FULLY PAID',
                'badge_text_pending' => 'PAYMENT PENDING',
                'show_pay_online_btn' => '1',
                'pay_btn_text' => 'PAY NOW - CLICK HERE',
                'footer_note' => 'Please visit our website for full digital terms & conditions.',
            ],
        ];
    }
}

