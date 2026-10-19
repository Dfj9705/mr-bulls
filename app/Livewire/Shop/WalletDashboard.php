<?php

namespace App\Livewire\Shop;

use Livewire\Component;
use Livewire\WithPagination;

class WalletDashboard extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public function render()
    {
        $wallet = auth()->user()
            ->wallet()
            ->first();

        $transactions = $wallet
            ? $wallet->transactions()
                ->with('order')
                ->latest('id')
                ->paginate(10)
            : null;

        return view('livewire.shop.wallet-dashboard', [
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }
}
