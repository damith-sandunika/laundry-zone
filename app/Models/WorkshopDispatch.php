<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopDispatch extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'received_at_workshop_at' => 'datetime',
        'completed_at_workshop_at' => 'datetime',
        'returned_at' => 'datetime',
        'received_at_branch_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withoutGlobalScopes();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function workshopBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'workshop_branch_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by')->withoutGlobalScopes();
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withoutGlobalScopes();
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by')->withoutGlobalScopes();
    }

    // All steps in the workflow
    public function steps(): array
    {
        return [
            ['key' => 'dispatched_to_workshop',  'label' => 'Dispatched to Workshop',   'icon' => '📤', 'time' => $this->dispatched_at],
            ['key' => 'received_at_workshop',     'label' => 'Received at Workshop',     'icon' => '📥', 'time' => $this->received_at_workshop_at],
            ['key' => 'processing',               'label' => 'Processing at Workshop',   'icon' => '⚙️',  'time' => null],
            ['key' => 'completed_at_workshop',    'label' => 'Completed at Workshop',    'icon' => '✅', 'time' => $this->completed_at_workshop_at],
            ['key' => 'returned_to_branch',       'label' => 'Returned to Branch',       'icon' => '🚚', 'time' => $this->returned_at],
            ['key' => 'received_at_branch',       'label' => 'Received at Branch',       'icon' => '🏪', 'time' => $this->received_at_branch_at],
        ];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'dispatched_to_workshop' => 'Dispatched to Workshop',
            'received_at_workshop' => 'Received at Workshop',
            'processing' => 'Processing',
            'completed_at_workshop' => 'Completed at Workshop',
            'returned_to_branch' => 'Returned to Branch',
            'received_at_branch' => 'Received at Branch',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'dispatched_to_workshop' => 'orange',
            'received_at_workshop' => 'blue',
            'processing' => 'yellow',
            'completed_at_workshop' => 'teal',
            'returned_to_branch' => 'purple',
            'received_at_branch' => 'green',
            default => 'gray',
        };
    }
}

