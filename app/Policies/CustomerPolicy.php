<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CustomerPolicy extends OperationsPolicy
{
    public function view(User $user, Model $record): bool
    {
        return $user->isStaff() || $record->user_id === $user->id;
    }

    public function update(User $user, Model $record): bool
    {
        return $user->isStaff() || $record->user_id === $user->id;
    }
}
