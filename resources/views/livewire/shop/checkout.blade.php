<div>

    <section class="bg-white border-bottom">
        <div class="container py-5">

            <h1 class="fw-bold mb-2">
                Finalizar compra
            </h1>

            <p class="text-muted mb-0">
                Completa tus datos para preparar tu pedido.
            </p>

        </div>
    </section>


    <section class="py-5">

        <div class="container">

            <div class="row g-4">

                {{-- ============================================
                FORMULARIO
                ============================================= --}}
                <div class="col-lg-8">


                    {{-- DATOS DEL COMPRADOR --}}
                    <div class="card border-0 shadow-sm mb-4">

                        <div class="card-body p-4">
                            @error('cart')

                                <div class="alert alert-danger">

                                    <i class="bx bx-error-circle me-2"></i>

                                    {{ $message }}

                                    <div class="mt-2">
                                        <a href="{{ route('cart.index') }}" class="alert-link">
                                            Revisar carrito
                                        </a>
                                    </div>

                                </div>

                            @enderror

                            <h4 class="fw-bold mb-4">
                                Datos del comprador
                            </h4>


                            <div class="row g-3">

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Nombre completo
                                    </label>

                                    <input type="text" class="form-control @error('customerName') is-invalid @enderror"
                                        wire:model.blur="customerName" autocomplete="name">
                                    @error('customerName')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Correo electrónico
                                    </label>

                                    <input type="email"
                                        class="form-control @error('customerEmail') is-invalid @enderror"
                                        wire:model.blur="customerEmail" autocomplete="email">
                                    @error('customerEmail')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Teléfono
                                    </label>

                                    <input type="tel" class="form-control @error('customerPhone') is-invalid @enderror"
                                        wire:model.blur="customerPhone" autocomplete="tel">
                                    @error('customerPhone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>
                            </div>

                        </div>

                    </div>


                    {{-- ============================================
                    DIRECCIONES GUARDADAS
                    ============================================= --}}

                    @auth

                        @if($addresses->isNotEmpty())

                            <div class="card border-0 shadow-sm mb-4">

                                <div class="card-body p-4">

                                    <h4 class="fw-bold mb-1">
                                        Dirección de entrega
                                    </h4>

                                    <p class="text-muted mb-4">
                                        Selecciona una dirección guardada.
                                    </p>


                                    <div class="row g-3">

                                        @foreach($addresses as $savedAddress)

                                                            <div class="col-md-6" wire:key="checkout-address-{{ $savedAddress->id }}">

                                                                <button type="button" wire:click="selectAddress({{ $savedAddress->id }})" class="checkout-address-card text-start w-100
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    {{ !$useNewAddress &&
                                            $selectedAddressId === $savedAddress->id
                                            ? 'active'
                                            : '' }}">

                                                                    <div
                                                                        class="d-flex
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           justify-content-between
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           align-items-start
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           gap-2">

                                                                        <strong>
                                                                            {{ $savedAddress->label ?: 'Dirección' }}
                                                                        </strong>


                                                                        @if($savedAddress->is_default)

                                                                            <span class="badge bg-primary">
                                                                                Principal
                                                                            </span>

                                                                        @endif

                                                                    </div>


                                                                    <div class="small mt-2">
                                                                        {{ $savedAddress->recipient_name }}
                                                                    </div>

                                                                    <div class="small text-muted">
                                                                        {{ $savedAddress->address }}
                                                                    </div>

                                                                    <div class="small text-muted">
                                                                        {{ $savedAddress->municipality }},
                                                                        {{ $savedAddress->department }}
                                                                    </div>

                                                                    <div class="small text-muted mt-2">
                                                                        {{ $savedAddress->phone }}
                                                                    </div>

                                                                </button>

                                                            </div>

                                        @endforeach


                                        {{-- NUEVA DIRECCIÓN --}}
                                        <div class="col-md-6">

                                            <button type="button" wire:click="useNewAddressForm"
                                                class="checkout-address-card
                                                                                                                                                                                                                                                                                                                                                               text-start w-100
                                                                                                                                                                                                                                                                                                                                                               {{ $useNewAddress ? 'active' : '' }}">

                                                <div class="d-flex align-items-center gap-2">

                                                    <i class="bx bx-plus-circle fs-4"></i>

                                                    <strong>
                                                        Usar otra dirección
                                                    </strong>

                                                </div>

                                                <div class="small text-muted mt-2">
                                                    Ingresa una dirección diferente
                                                    para este pedido.
                                                </div>

                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        @endif

                    @endauth


                    {{-- ============================================
                    FORMULARIO DIRECCIÓN
                    ============================================= --}}

                    @if($useNewAddress)

                        <div class="card border-0 shadow-sm mb-4">

                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-4">
                                    Dirección de entrega
                                </h4>


                                <div class="row g-3">

                                    <div class="col-md-6">

                                        <label class="form-label">
                                            Nombre de quien recibe
                                        </label>

                                        <input type="text"
                                            class="form-control @error('shippingRecipient') is-invalid @enderror"
                                            wire:model.blur="shippingRecipient" autocomplete="name">

                                        @error('shippingRecipient')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">

                                        <label class="form-label">
                                            Teléfono de entrega
                                        </label>

                                        <input type="tel" class="form-control @error('shippingPhone') is-invalid @enderror"
                                            wire:model.blur="shippingPhone" autocomplete="tel">

                                        @error('shippingPhone')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- DEPARTAMENTO --}}
                                    <div class="col-md-6">

                                        <label class="form-label">
                                            Departamento
                                        </label>

                                        <select class="form-select @error('department') is-invalid @enderror"
                                            wire:model.live="department">

                                            <option value="">
                                                Selecciona...
                                            </option>

                                            @foreach($departments as $value => $label)

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

                                    {{-- MUNICIPIO --}}
                                    <div class="col-md-6">

                                        <label class="form-label">
                                            Municipio
                                        </label>

                                        <select class="form-select @error('municipality') is-invalid @enderror"
                                            wire:model.live="municipality" @disabled(blank($department))>

                                            <option value="">
                                                Selecciona...
                                            </option>

                                            @foreach($municipalities as $value => $label)

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

                                    {{-- DIRECCIÓN --}}
                                    <div class="col-12">

                                        <label class="form-label">
                                            Dirección
                                        </label>

                                        <input type="text" class="form-control" wire:model.blur="address"
                                            placeholder="Zona, colonia, calle, avenida, número..."
                                            autocomplete="street-address">

                                        @error('address')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- REFERENCIAS --}}
                                    <div class="col-12">

                                        <label class="form-label">
                                            Referencias
                                            <span class="text-muted">
                                                (opcional)
                                            </span>
                                        </label>

                                        <textarea class="form-control" wire:model.blur="references" rows="3"
                                            placeholder="Color de casa, portón, comercio cercano..."></textarea>

                                        @error('references')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- NOTAS --}}
                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-3">
                                Notas del pedido
                            </h4>

                            <textarea class="form-control" rows="3" wire:model.blur="customerNotes"
                                placeholder="Indicaciones adicionales para tu pedido..."></textarea>

                            @error('customerNotes')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                </div>


                {{-- ============================================
                RESUMEN
                ============================================= --}}
                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm" style="position: sticky; top: 100px;">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-4">
                                Tu pedido
                            </h4>


                            @foreach($items as $item)

                                                        @php
                                                            $product = $item['product'];
                                                        @endphp

                                                        <div
                                                            class="d-flex gap-3 py-3
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       {{ !$loop->last ? 'border-bottom' : '' }}">

                                                            @if($product->main_image)

                                                                <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}"
                                                                    width="60" height="60" class="rounded object-fit-cover flex-shrink-0">

                                                            @endif


                                                            <div class="flex-grow-1">

                                                                <div class="fw-semibold">
                                                                    {{ $product->name }}
                                                                </div>

                                                                <small class="text-muted">
                                                                    {{ $item['quantity'] }}
                                                                    ×
                                                                    Q{{ number_format($product->price, 2) }}
                                                                </small>

                                                            </div>


                                                            <div class="fw-semibold">

                                                                Q{{ number_format(
                                    $item['subtotal'],
                                    2
                                ) }}

                                                            </div>

                                                        </div>

                            @endforeach


                            <hr>


                            <div class="d-flex justify-content-between mb-3">

                                <span class="text-muted">
                                    Subtotal
                                </span>

                                <span>
                                    Q{{ number_format($subtotal, 2) }}
                                </span>

                            </div>


                            <div class="d-flex justify-content-between mb-3">

                                <span class="text-muted">
                                    Envío
                                </span>

                                <span class="text-success fw-bold">
                                    GRATIS (Q 0.00)
                                </span>

                            </div>


                            @if (auth()->check() && (float) $walletBalance > 0)
                                <div class="card border mb-3">
                                    <div class="card-body">

                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <i class="bx bx-wallet fs-3 text-primary"></i>
                                            <div>
                                                <h6 class="mb-0">Mi monedero Mr Bulls</h6>
                                                <small class="text-muted">
                                                    Utiliza tus recompensas acumuladas
                                                </small>
                                            </div>
                                        </div>

                                        <div class="bg-label-success rounded p-3 mb-3">
                                            <small>Saldo disponible</small>
                                            <h4 class="mb-0">
                                                Q{{ number_format((float) $walletBalance, 2) }}
                                            </h4>
                                        </div>

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="useWallet"
                                                wire:model.live="useWallet">

                                            <label class="form-check-label" for="useWallet">
                                                Utilizar mi saldo en esta compra
                                            </label>
                                        </div>

                                        @if ($useWallet)
                                            <label class="form-label">
                                                ¿Cuánto saldo deseas utilizar?
                                            </label>

                                            <div class="input-group">
                                                <span class="input-group-text">Q</span>

                                                <input type="number" class="form-control" min="0.01" max="{{ $walletBalance }}"
                                                    step="0.01" wire:model.live.debounce.300ms="walletAmount">
                                            </div>

                                            @error('walletAmount')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror

                                            @error('wallet')
                                                <small class="text-danger d-block mt-2">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        @endif

                                    </div>
                                </div>
                            @endif



                            <hr>




                            @if ($walletDiscount > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Saldo Mr Bulls utilizado</span>
                                    <span>
                                        -Q{{ number_format($walletDiscount, 2) }}
                                    </span>
                                </div>
                            @endif

                            <hr>

                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total a pagar</span>
                                <span>Q{{ number_format($finalTotal, 2) }}</span>
                            </div>



                            <button type="button" class="btn btn-primary btn-lg w-100" wire:click="createOrder"
                                wire:loading.attr="disabled" wire:target="createOrder">

                                <span wire:loading.remove wire:target="createOrder">
                                    Confirmar pedido
                                </span>

                                <span wire:loading wire:target="createOrder">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>

                                    Procesando...
                                </span>

                            </button>


                            <div class="text-center mt-3">

                                <a href="{{ route('cart.index') }}" class="small text-muted">
                                    Volver al carrito
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>