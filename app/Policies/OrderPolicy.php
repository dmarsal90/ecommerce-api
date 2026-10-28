<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->id !== $order->user_id && ! $user->hasRole('admin')) {
            return false;
        }

        return in_array($order->status, ['pending', 'processing']);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }
}
