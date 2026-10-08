<div>

    <section class="bg-white border-bottom">
        <div class="container py-5">

            <span class="text-muted">
                Mi cuenta
            </span>

            <h1 class="fw-bold mb-1">
                Mis direcciones
            </h1>

            <p class="text-muted mb-0">
                Administra las direcciones que puedes utilizar en tus pedidos.
            </p>

        </div>
    </section>

    <section class="py-5">
        <div class="container">

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row g-4">

                {{-- DIRECCIONES --}}
                <div class="col-lg-7">

                    <h4 class="fw-bold mb-3">
                        Direcciones guardadas
                    </h4>

                    @forelse ($addresses as $savedAddress)

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-4">

                                <div class="d-flex justify-content-between gap-3">

                                    <div>

                                        <div class="d-flex align-items-center gap-2 mb-2">

                                            <h5 class="fw-bold mb-0">
                                                {{ $savedAddress->label ?: 'Dirección' }}
                                            </h5>

                                            @if ($savedAddress->is_default)
                                                <span class="badge bg-success">
                                                    Predeterminada
                                                </span>
                                            @endif

                                        </div>

                                        <div class="fw-semibold">
                                            {{ $savedAddress->recipient_name }}
                                        </div>

                                        <div class="text-muted">
                                            {{ $savedAddress->phone }}
                                        </div>

                                        <div class="mt-2">
                                            {{ $savedAddress->address }}
                                        </div>

                                        <div class="text-muted">
                                            {{ $savedAddress->municipality }},
                                            {{ $savedAddress->department }}
                                        </div>

                                        @if ($savedAddress->references)
                                            <div class="small text-muted mt-2">
                                                {{ $savedAddress->references }}
                                            </div>
                                        @endif

                                    </div>

                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4">

                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        wire:click="edit({{ $savedAddress->id }})">
                                        Editar
                                    </button>

                                    @unless ($savedAddress->is_default)
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            wire:click="setDefault({{ $savedAddress->id }})">
                                            Hacer predeterminada
                                        </button>
                                    @endunless

                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        wire:click="delete({{ $savedAddress->id }})"
                                        wire:confirm="¿Deseas eliminar esta dirección?">
                                        Eliminar
                                    </button>

                                </div>

                            </div>
                        </div>

                    @empty

                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 text-center">

                                <i class="bx bx-map fs-1 text-muted"></i>

                                <h5 class="fw-bold mt-3">
                                    No tienes direcciones guardadas
                                </h5>

                                <p class="text-muted mb-0">
                                    Agrega tu primera dirección para utilizarla
                                    durante el checkout.
                                </p>

                            </div>
                        </div>

                    @endforelse

                </div>


                {{-- FORMULARIO --}}
                <div class="col-lg-5">

                    <div class="card border-0 shadow-sm" style="position: sticky; top: 100px;">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-4">
                                {{ $editingId
    ? 'Editar dirección'
    : 'Agregar dirección'
                                }}
                            </h4>

                            <form wire:submit="save">

                                <div class="mb-3">
                                    <label class="form-label">
                                        Nombre de la dirección
                                    </label>

                                    <input type="text" class="form-control" placeholder="Casa, Trabajo..."
                                        wire:model="label">

                                    @error('label')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Nombre de quien recibe
                                    </label>

                                    <input type="text" class="form-control" wire:model="recipient_name">

                                    @error('recipient_name')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Teléfono
                                    </label>

                                    <input type="text" class="form-control" wire:model="phone">

                                    @error('phone')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="row g-3 mb-3">

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Departamento
                                        </label>

                                        <select class="form-select @error('department') is-invalid @enderror"
                                            wire:model.live="department">
                                            <option value="">
                                                Selecciona...
                                            </option>

                                            @foreach ($departments as $value => $label)
                                                <option value="{{ $value }}">
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('department')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Municipio
                                        </label>

                                        <select class="form-select @error('municipality') is-invalid @enderror"
                                            wire:model="municipality" @disabled(blank($department))>
                                            <option value="">
                                                Selecciona...
                                            </option>

                                            @foreach ($municipalities as $value => $label)
                                                <option value="{{ $value }}">
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('municipality')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Dirección
                                    </label>

                                    <input type="text" class="form-control" wire:model="address">

                                    @error('address')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">
                                        Referencias
                                    </label>

                                    <textarea class="form-control" rows="3" wire:model="references"></textarea>

                                    @error('references')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-check mb-4">

                                    <input type="checkbox" id="is_default" class="form-check-input"
                                        wire:model="is_default">

                                    <label for="is_default" class="form-check-label">
                                        Usar como dirección predeterminada
                                    </label>

                                </div>

                                <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled"
                                    wire:target="save">
                                    {{ $editingId
    ? 'Guardar cambios'
    : 'Agregar dirección'
                                    }}
                                </button>

                                @if ($editingId)

                                    <button type="button" class="btn btn-outline-secondary w-100 mt-2"
                                        wire:click="cancelEdit">
                                        Cancelar edición
                                    </button>

                                @endif

                            </form>

                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

</div>