<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser()) {
            $user = Auth::user();

            // Skip scope for Customer model instances (they are not branch-scoped staff users)
            if ($user instanceof \App\Models\Customer) {
                return;
            }

            // Allow super_admin to see all branches' data
            if ($user->role !== 'super_admin') {
                if ($model instanceof \App\Models\Order) {
                    $builder->where(function($q) use ($model, $user) {
                        $q->where($model->getTable().'.branch_id', $user->branch_id)
                          ->orWhere($model->getTable().'.workshop_id', $user->branch_id);
                    });
                } else {
                    $builder->where($model->getTable().'.branch_id', $user->branch_id);
                }
            }
        }
    }
}
