<?php

namespace App\Livewire\Shop;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MyOrders extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $orders = Auth::user()
            ->orders()
            ->latest()
            ->paginate(10);

        return view('livewire.shop.my-orders', [
            'orders' => $orders,
        ]);
    }
}