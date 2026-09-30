<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\MessageTemplate;
use App\Models\PromotionalCampaign;
use App\Jobs\SendCampaignMessagesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PromotionalCampaignController extends Controller
{
    public function index()
    {
        Gate::authorize('is-super-admin');
        $campaigns = PromotionalCampaign::with(['branch', 'creator'])->latest()->paginate(15);
        return view('promotional_campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        Gate::authorize('is-super-admin');
        $branches = Branch::all();
        return view('promotional_campaigns.create', compact('branches'));
    }

    public function store(Request $request)
    {
        Gate::authorize('is-super-admin');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,email,whatsapp',
            'audience_type' => 'required|in:all,top_customers,inactive,branch_specific,birthday',
            'branch_id' => 'nullable|required_if:audience_type,branch_specific|exists:branches,id',
            'min_spent' => 'nullable|numeric|min:0',
            'min_orders' => 'nullable|integer|min:0',
        ]);

        $audienceConditions = [];
        if ($validated['min_spent']) $audienceConditions['min_spent'] = $validated['min_spent'];
        if ($validated['min_orders']) $audienceConditions['min_orders'] = $validated['min_orders'];

        $campaign = PromotionalCampaign::create([
            'name' => $validated['name'],
            'channel' => $validated['channel'],
            'audience_type' => $validated['audience_type'],
            'branch_id' => $validated['branch_id'] ?? null,
            'audience_conditions' => json_encode($audienceConditions),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        // Dispatch the background job to find recipients and send messages
        SendCampaignMessagesJob::dispatch($campaign);

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign created and queued for sending.');
    }
    
    public function show(PromotionalCampaign $campaign)
    {
        Gate::authorize('is-super-admin');
        $campaign->load(['branch', 'creator']);
        return view('promotional_campaigns.show', compact('campaign'));
    }

    public function destroy(PromotionalCampaign $campaign)
    {
        Gate::authorize('is-super-admin');
        if ($campaign->status === 'processing') {
            return back()->with('error', 'Cannot delete a campaign that is currently processing.');
        }
        $campaign->delete();
        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }
}
