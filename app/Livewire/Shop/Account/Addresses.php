<?php

namespace App\Livewire\Shop\Account;

use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Support\GuatemalaLocations;

class Addresses extends Component
{
    public ?int $editingId = null;

    public string $label = '';
    public string $recipient_name = '';
    public string $phone = '';
    public string $department = '';
    public string $municipality = '';
    public string $address = '';
    public string $references = '';
    public bool $is_default = false;

    public function mount(): void
    {
        $this->recipient_name = Auth::user()->name;
    }

    protected function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'department' => [
                'required',
                'string',
                Rule::in(array_keys(
                    GuatemalaLocations::departments()
                )),
            ],

            'municipality' => [
                'required',
                'string',
                Rule::in(array_keys(
                    GuatemalaLocations::municipalitiesFor(
                        $this->department
                    )
                )),
            ],
            'address' => ['required', 'string', 'max:255'],
            'references' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $user = Auth::user();

        /*
         * Si es la primera dirección del usuario,
         * automáticamente será la predeterminada.
         */
        if (!$user->addresses()->exists()) {
            $validated['is_default'] = true;
        }

        if ($this->editingId) {

            /*
             * IMPORTANTE:
             * buscamos la dirección exclusivamente
             * dentro de las direcciones del usuario.
             */
            $address = $user->addresses()
                ->whereKey($this->editingId)
                ->firstOrFail();

            /*
             * Evitamos dejar al usuario sin dirección
             * predeterminada al editar la actual.
             */
            if (
                $address->is_default
                && !$validated['is_default']
                && !$user->addresses()
                    ->whereKeyNot($address->id)
                    ->where('is_default', true)
                    ->exists()
            ) {
                $validated['is_default'] = true;
            }

            $address->update($validated);

            $message = 'Dirección actualizada correctamente.';

        } else {

            $user->addresses()->create($validated);

            $message = 'Dirección agregada correctamente.';
        }

        $this->resetForm();

        session()->flash('success', $message);
    }

    public function edit(int $id): void
    {
        $address = Auth::user()
            ->addresses()
            ->whereKey($id)
            ->firstOrFail();

        $this->editingId = $address->id;
        $this->label = $address->label ?? '';
        $this->recipient_name = $address->recipient_name;
        $this->phone = $address->phone;
        $this->department = $address->department;
        $this->municipality = $address->municipality;
        $this->address = $address->address;
        $this->references = $address->references ?? '';
        $this->is_default = $address->is_default;

        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function setDefault(int $id): void
    {
        $address = Auth::user()
            ->addresses()
            ->whereKey($id)
            ->firstOrFail();

        $address->update([
            'is_default' => true,
        ]);

        session()->flash(
            'success',
            'Dirección predeterminada actualizada.'
        );
    }

    public function delete(int $id): void
    {
        $user = Auth::user();

        $address = $user->addresses()
            ->whereKey($id)
            ->firstOrFail();

        $wasDefault = $address->is_default;

        $address->delete();

        /*
         * Si eliminamos la predeterminada,
         * seleccionamos otra automáticamente.
         */
        if ($wasDefault) {
            $nextAddress = $user->addresses()
                ->latest()
                ->first();

            if ($nextAddress) {
                $nextAddress->update([
                    'is_default' => true,
                ]);
            }
        }

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        session()->flash(
            'success',
            'Dirección eliminada correctamente.'
        );
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'label',
            'phone',
            'department',
            'municipality',
            'address',
            'references',
            'is_default',
        ]);

        /*
         * Dejamos preparado el nombre del usuario
         * para registrar otra dirección.
         */
        $this->recipient_name = Auth::user()->name;

        $this->resetValidation();
    }

    public function updatedDepartment(): void
    {
        $this->municipality = '';
    }

    public function render()
    {
        return view('livewire.shop.account.addresses', [
            'addresses' => Auth::user()
                ->addresses()
                ->orderByDesc('is_default')
                ->latest()
                ->get(),

            'departments' => GuatemalaLocations::departments(),

            'municipalities' => GuatemalaLocations::municipalitiesFor(
                $this->department
            ),
        ]);
    }
}