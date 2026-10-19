<div>
    @php
        $balance = (float) ($wallet?->balance ?? 0);

        $movementTypes = [
            'credit' => [
                'label' => 'Recompensa por compra',
                'icon' => 'bx-gift',
                'class' => 'text-success',
            ],
            'debit' => [
                'label' => 'Saldo utilizado',
                'icon' => 'bx-shopping-bag',
                'class' => 'text-danger',
            ],
            'refund' => [
                'label' => 'Devolución de saldo',
                'icon' => 'bx-undo',
                'class' => 'text-success',
            ],
            'reversal' => [
                'label' => 'Reversión de recompensa',
                'icon' => 'bx-refresh',
                'class' => 'text-danger',
            ],
            'adjustment' => [
                'label' => 'Ajuste de saldo',
                'icon' => 'bx-slider',
                'class' => 'text-secondary',
            ],
        ];
    @endphp

    <div class="row g-4">

        {{-- Saldo disponible --}}
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bx bx-wallet fs-3 text-primary"></i>
                        <h2 class="h5 mb-0">
                            Saldo disponible
                        </h2>
                    </div>

                    <div class="display-5 fw-bold text-success mb-2">
                        Q {{ number_format($balance, 2) }}
                    </div>

                    <p class="text-muted mb-4">
                        Puedes utilizar este saldo para pagar
                        tus próximas compras en Mr Bulls.
                    </p>

                    <div class="rounded bg-label-primary p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bx bx-gift fs-4"></i>
                            <strong>Mr Bulls Rewards</strong>
                        </div>

                        <p class="small mb-0">
                            Recibe el 2% del importe elegible
                            de tus compras pagadas.
                            Las recompensas se acreditan
                            en tu monedero.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Historial --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">
                        <i class="bx bx-history me-1"></i>
                        Historial de movimientos
                    </h2>
                </div>

                <div class="card-body">

                    @if (!$transactions || $transactions->isEmpty())

                        <div class="text-center py-5">
                            <i class="bx bx-receipt fs-1 text-muted"></i>

                            <p class="text-muted mt-3 mb-0">
                                Todavía no tienes movimientos
                                en tu monedero.
                            </p>
                        </div>

                    @else

                        <div class="list-group list-group-flush">

                            @foreach ($transactions as $transaction)
                                @php
                                    $type = $movementTypes[$transaction->type]
                                        ?? [
                                            'label' => 'Movimiento',
                                            'icon' => 'bx-transfer',
                                            'class' => 'text-secondary',
                                        ];

                                    $before = (float) $transaction->balance_before;
                                    $after = (float) $transaction->balance_after;
                                    $difference = round($after - $before, 2);
                                @endphp

                                <div class="list-group-item px-0 py-3">

                                    <div class="d-flex align-items-start gap-3">

                                        <div class="rounded-circle bg-light p-2">
                                            <i class="bx {{ $type['icon'] }} fs-4"></i>
                                        </div>

                                        <div class="flex-grow-1">

                                            <div class="fw-semibold">
                                                {{ $type['label'] }}
                                            </div>

                                            @if ($transaction->description)
                                                <div class="small text-muted">
                                                    {{ $transaction->description }}
                                                </div>
                                            @endif

                                            @if ($transaction->order)
                                                <div class="small text-muted">
                                                    Pedido:
                                                    {{ $transaction->order->order_number }}
                                                </div>
                                            @endif

                                            <div class="small text-muted mt-1">
                                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                                            </div>

                                        </div>

                                        <div class="text-end">

                                            <div class="fw-bold {{ $difference >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $difference >= 0 ? '+' : '-' }}
                                                Q {{ number_format(abs($difference), 2) }}
                                            </div>

                                            <div class="small text-muted">
                                                Saldo:
                                                Q {{ number_format((float) $transaction->balance_after, 2) }}
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>

                    @endif

                </div>
            </div>
        </div>

    </div>
</div>