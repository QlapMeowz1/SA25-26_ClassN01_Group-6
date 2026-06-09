<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function change(
        User $user,
        int $amount,
        string $type,
        ?string $reference = null,
        ?string $description = null,
        ?Bet $bet = null,
        ?User $actor = null,
        array $metadata = []
    ): WalletTransaction {
        return DB::transaction(function () use ($user, $amount, $type, $reference, $description, $bet, $actor, $metadata) {
            if ($reference && WalletTransaction::where('reference', $reference)->exists()) {
                return WalletTransaction::where('reference', $reference)->firstOrFail();
            }

            $lockedUser = User::withTrashed()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $before = (int) $lockedUser->virtual_coins;
            $after = $before + $amount;

            if ($after < 0) {
                throw new \InvalidArgumentException('Insufficient coins');
            }

            $lockedUser->update(['virtual_coins' => $after]);

            return WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'actor_id' => $actor?->id,
                'bet_id' => $bet?->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'reference' => $reference,
                'description' => $description,
                'metadata' => $metadata ?: null,
            ]);
        });
    }

    public function set(User $user, int $balance, string $description, ?User $actor = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $balance, $description, $actor) {
            $lockedUser = User::withTrashed()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $before = (int) $lockedUser->virtual_coins;
            $lockedUser->update(['virtual_coins' => $balance]);

            return WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'actor_id' => $actor?->id,
                'type' => 'admin_adjustment',
                'amount' => $balance - $before,
                'balance_before' => $before,
                'balance_after' => $balance,
                'reference' => 'admin-adjustment-' . $lockedUser->id . '-' . now()->format('YmdHisv'),
                'description' => $description,
            ]);
        });
    }
}
