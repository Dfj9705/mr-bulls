<?php

namespace App\Livewire\Shop;

use App\Models\Address;
use App\Services\CartService;
use App\Support\GuatemalaLocations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Checkout extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Comprador
    |--------------------------------------------------------------------------
    */

    public string $customerName = '';

    public string $customerEmail = '';

    public string $customerPhone = '';


    /*
    |--------------------------------------------------------------------------
    | Dirección
    |--------------------------------------------------------------------------
    */

    public ?int $selectedAddressId = null;

    public bool $useNewAddress = true;

    public string $department = '';

    public string $municipality = '';

    public string $address = '';

    public string $references = '';


    /*
    |--------------------------------------------------------------------------
    | Pedido
    |--------------------------------------------------------------------------
    */

    public string $customerNotes = '';


    public function mount(CartService $cart): void
    {
        /*
         * No tiene sentido entrar al checkout
         * con el carrito vacío.
         */
        if ($cart->items()->isEmpty()) {
            $this->redirectRoute('cart.index');

            return;
        }

        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        $this->customerName = $user->name;
        $this->customerEmail = $user->email;

        /*
         * Buscamos dirección predeterminada.
         */
        $defaultAddress = $user->addresses()
            ->where('is_default', true)
            ->first();

        if ($defaultAddress) {

            $this->selectedAddressId = $defaultAddress->id;

            $this->useNewAddress = false;

            $this->customerPhone = $defaultAddress->phone;

            $this->loadAddress($defaultAddress);
        }
    }


    public function selectAddress(int $addressId): void
    {
        if (!Auth::check()) {
            return;
        }

        $address = Auth::user()
            ->addresses()
            ->find($addressId);

        if (!$address) {
            return;
        }

        $this->selectedAddressId = $address->id;

        $this->useNewAddress = false;

        $this->customerPhone = $address->phone;

        $this->loadAddress($address);
    }


    public function useNewAddressForm(): void
    {
        $this->selectedAddressId = null;

        $this->useNewAddress = true;

        $this->department = '';
        $this->municipality = '';
        $this->address = '';
        $this->references = '';
    }


    public function updatedDepartment(): void
    {
        /*
         * Evitamos conservar un municipio de
         * otro departamento.
         */
        $this->municipality = '';
    }


    private function loadAddress(Address $address): void
    {
        $this->department = $address->department;
        $this->municipality = $address->municipality;
        $this->address = $address->address;
        $this->references = $address->references ?? '';
    }


    public function render(CartService $cart)
    {
        $addresses = collect();

        if (Auth::check()) {
            $addresses = Auth::user()
                ->addresses()
                ->orderByDesc('is_default')
                ->latest()
                ->get();
        }

        return view('livewire.shop.checkout', [
            'items' => $cart->items(),

            'subtotal' => $cart->subtotal(),

            'addresses' => $addresses,

            'departments' => GuatemalaLocations::departments(),

            'municipalities' =>
                GuatemalaLocations::municipalitiesFor(
                    $this->department
                ),
        ]);
    }
}