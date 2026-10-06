@extends('layouts.shop')

@section('title', 'Pedido ' . $order->order_number . ' | Mr Bulls')

@section('content')

<section class="bg-white border-bottom">

    <div class="container py-5">

        <div
            class="d-flex flex-column flex-md-row
                   justify-content-between align-items-md-center gap-3"
        >

            <div>

                <span class="text-muted">
                    Pedido
                </span>

                <h1 class="fw-bold mb-1">
                    {{ $order->order_number }}
                </h1>

                <span class="text-muted">
                    Realizado el
                    {{ $order->created_at->format('d/m/Y H:i') }}
                </span>

            </div>


            <div>

                @switch($order->status)

                    @case('pending')
                        <span class="badge bg-warning fs-6">
                            Pendiente
                        </span>
                        @break

                    @case('processing')
                        <span class="badge bg-info fs-6">
                            Procesando
                        </span>
                        @break

                    @case('shipped')
                        <span class="badge bg-primary fs-6">
                            Enviado
                        </span>
                        @break

                    @case('completed')
                        <span class="badge bg-success fs-6">
                            Completado
                        </span>
                        @break

                    @case('cancelled')
                        <span class="badge bg-danger fs-6">
                            Cancelado
                        </span>
                        @break

                @endswitch

            </div>

        </div>

    </div>

</section>


<section class="py-5">

    <div class="container">

        <div class="row g-4">

            {{-- =====================================
                 COLUMNA PRINCIPAL
            ====================================== --}}
            <div class="col-lg-8">


                {{-- ESTADO --}}
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            Estado del pedido
                        </h4>


                        @if($order->status === 'cancelled')

                            <div class="alert alert-danger mb-0">

                                <i class="bx bx-x-circle me-2"></i>

                                Este pedido fue cancelado.

                            </div>

                        @else

                            @php
                                $steps = [
                                    'pending' => 1,
                                    'processing' => 2,
                                    'shipped' => 3,
                                    'completed' => 4,
                                ];

                                $currentStep =
                                    $steps[$order->status] ?? 1;
                            @endphp


                            <div class="order-progress">

                                {{-- RECIBIDO --}}
                                <div
                                    class="order-progress-item
                                    {{ $currentStep >= 1 ? 'active' : '' }}"
                                >
                                    <div class="order-progress-icon">
                                        <i class="bx bx-receipt"></i>
                                    </div>

                                    <div>
                                        <strong>Recibido</strong>

                                        <small>
                                            Pedido registrado
                                        </small>
                                    </div>
                                </div>


                                {{-- PROCESANDO --}}
                                <div
                                    class="order-progress-item
                                    {{ $currentStep >= 2 ? 'active' : '' }}"
                                >
                                    <div class="order-progress-icon">
                                        <i class="bx bx-package"></i>
                                    </div>

                                    <div>
                                        <strong>Procesando</strong>

                                        <small>
                                            Preparando tu pedido
                                        </small>
                                    </div>
                                </div>


                                {{-- ENVIADO --}}
                                <div
                                    class="order-progress-item
                                    {{ $currentStep >= 3 ? 'active' : '' }}"
                                >
                                    <div class="order-progress-icon">
                                        <i class="bx bx-car"></i>
                                    </div>

                                    <div>
                                        <strong>Enviado</strong>

                                        <small>
                                            Pedido en camino
                                        </small>
                                    </div>
                                </div>


                                {{-- COMPLETADO --}}
                                <div
                                    class="order-progress-item
                                    {{ $currentStep >= 4 ? 'active' : '' }}"
                                >
                                    <div class="order-progress-icon">
                                        <i class="bx bx-check"></i>
                                    </div>

                                    <div>
                                        <strong>Completado</strong>

                                        <small>
                                            Pedido entregado
                                        </small>
                                    </div>
                                </div>

                            </div>

                        @endif

                    </div>

                </div>


                {{-- PRODUCTOS --}}
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-3">
                            Productos
                        </h4>


                        @foreach($order->items as $item)

                            <div
                                class="d-flex justify-content-between
                                       align-items-center gap-3 py-3
                                       {{ ! $loop->last ? 'border-bottom' : '' }}"
                            >

                                <div>

                                    <div class="fw-semibold">
                                        {{ $item->product_name }}
                                    </div>

                                    <small class="text-muted">
                                        SKU: {{ $item->product_sku }}
                                    </small>

                                    <div class="small text-muted mt-1">
                                        {{ $item->quantity }}
                                        ×
                                        Q{{ number_format($item->unit_price, 2) }}
                                    </div>

                                </div>


                                <div class="fw-bold text-nowrap">

                                    Q{{ number_format(
                                        $item->subtotal,
                                        2
                                    ) }}

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>


                {{-- ENTREGA --}}
                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            Información de entrega
                        </h4>


                        <div class="row g-4">

                            <div class="col-md-6">

                                <span class="text-muted small">
                                    Recibe
                                </span>

                                <div class="fw-semibold">
                                    {{ $order->shipping_recipient }}
                                </div>

                                <div>
                                    {{ $order->shipping_phone }}
                                </div>

                            </div>


                            <div class="col-md-6">

                                <span class="text-muted small">
                                    Dirección
                                </span>

                                <div class="fw-semibold">
                                    {{ $order->shipping_address }}
                                </div>

                                <div>
                                    {{ $order->shipping_municipality }},
                                    {{ $order->shipping_department }}
                                </div>

                            </div>


                            @if($order->shipping_references)

                                <div class="col-12">

                                    <span class="text-muted small">
                                        Referencias
                                    </span>

                                    <div>
                                        {{ $order->shipping_references }}
                                    </div>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- =====================================
                 RESUMEN
            ====================================== --}}
            <div class="col-lg-4">

                <div
                    class="card border-0 shadow-sm"
                    style="position: sticky; top: 100px;"
                >

                    <div class="card-body p-4">

                        <h4 class="fw-bold mb-4">
                            Resumen
                        </h4>


                        <div
                            class="d-flex justify-content-between mb-3"
                        >
                            <span class="text-muted">
                                Subtotal
                            </span>

                            <span>
                                Q{{ number_format($order->subtotal, 2) }}
                            </span>
                        </div>


                        <div
                            class="d-flex justify-content-between mb-3"
                        >
                            <span class="text-muted">
                                Envío
                            </span>

                            <span>
                                Q{{ number_format(
                                    $order->shipping_cost,
                                    2
                                ) }}
                            </span>
                        </div>


                        <hr>


                        <div
                            class="d-flex justify-content-between
                                   align-items-center mb-4"
                        >
                            <strong class="fs-5">
                                Total
                            </strong>

                            <strong class="fs-4">
                                Q{{ number_format($order->total, 2) }}
                            </strong>
                        </div>


                        {{-- PAGO --}}
                        <div class="mb-4">

                            <span class="text-muted small">
                                Estado del pago
                            </span>

                            <div class="mt-1">

                                @if($order->payment_status === 'paid')

                                    <span class="badge bg-success">
                                        Pagado
                                    </span>

                                @elseif($order->payment_status === 'pending')

                                    <span class="badge bg-warning">
                                        Pendiente
                                    </span>

                                @elseif($order->payment_status === 'failed')

                                    <span class="badge bg-danger">
                                        Fallido
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        Cancelado
                                    </span>

                                @endif

                            </div>

                        </div>


                        @if(
                            $order->payment_status === 'pending'
                            && $order->payment_url
                        )

                            <a
                                href="{{ $order->payment_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-success btn-lg w-100 mb-3"
                            >
                                <i class="bx bx-credit-card me-2"></i>

                                Pagar pedido
                            </a>

                        @elseif(
                            $order->payment_status === 'pending'
                            && ! $order->payment_url
                        )

                            <div class="alert alert-warning small">

                                <i class="bx bx-time-five me-1"></i>

                                Estamos preparando la información
                                para realizar el pago.

                            </div>

                        @elseif($order->payment_status === 'paid')

                            <div class="alert alert-success small">

                                <i class="bx bx-check-circle me-1"></i>

                                El pago de este pedido ya fue
                                registrado.

                            </div>

                        @endif


                        @auth

                            @if($order->user_id === auth()->id())

                                <a
                                    href="{{ route('account.orders') }}"
                                    class="btn btn-outline-secondary w-100"
                                >
                                    Mis pedidos
                                </a>

                            @endif

                        @endauth

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection