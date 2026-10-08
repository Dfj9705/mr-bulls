<div>

    <section class="bg-white border-bottom">
        <div class="container py-5">

            <h1 class="fw-bold mb-2">
                Mis pedidos
            </h1>

            <p class="text-muted mb-0">
                Consulta el estado de tus compras.
            </p>

        </div>
    </section>


    <section class="py-5">

        <div class="container">

            @if($orders->isNotEmpty())

                <div class="card border-0 shadow-sm">

                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Pago</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($orders as $order)

                                    <tr wire:key="order-{{ $order->id }}">

                                        <td class="fw-semibold">
                                            {{ $order->order_number }}
                                        </td>

                                        <td>
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </td>

                                        <td class="fw-semibold">
                                            Q{{ number_format($order->total, 2) }}
                                        </td>

                                        <td>
                                            @switch($order->status)

                                                @case('pending')
                                                    <span class="badge bg-warning">
                                                        Pendiente
                                                    </span>
                                                    @break

                                                @case('processing')
                                                    <span class="badge bg-info">
                                                        Procesando
                                                    </span>
                                                    @break

                                                @case('shipped')
                                                    <span class="badge bg-primary">
                                                        Enviado
                                                    </span>
                                                    @break

                                                @case('completed')
                                                    <span class="badge bg-success">
                                                        Completado
                                                    </span>
                                                    @break

                                                @case('cancelled')
                                                    <span class="badge bg-danger">
                                                        Cancelado
                                                    </span>
                                                    @break

                                            @endswitch
                                        </td>

                                        <td>
                                            @if($order->payment_status === 'paid')

                                                <span class="badge bg-success">
                                                    Pagado
                                                </span>

                                            @elseif($order->payment_status === 'pending')

                                                <span class="badge bg-warning">
                                                    Pendiente
                                                </span>

                                            @else

                                                <span class="badge bg-secondary">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>

                                            @endif
                                        </td>

                                        <td class="text-end">

                                            <a
                                               href="{{ route('account.orders.show', [
    'order' => $order
]) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Ver pedido
                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>


                <div class="mt-4">
                    {{ $orders->links() }}
                </div>

            @else

                <div class="card border-0 shadow-sm">

                    <div class="card-body text-center py-5">

                        <i
                            class="bx bx-package text-muted"
                            style="font-size: 4rem;"
                        ></i>

                        <h4 class="fw-bold mt-3">
                            Todavía no tienes pedidos
                        </h4>

                        <p class="text-muted">
                            Cuando realices una compra aparecerá aquí.
                        </p>

                        <a
                            href="{{ route('products.index') }}"
                            class="btn btn-primary"
                        >
                            Ver productos
                        </a>

                    </div>

                </div>

            @endif

        </div>

    </section>

</div>