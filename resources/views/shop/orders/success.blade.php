@extends('layouts.shop')

@section('title', 'Pedido recibido | Mr Bulls')

@section('content')

    <section class="py-5">

        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-7">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body p-5 text-center">

                            <i class="bx bx-check-circle text-success mb-3" style="font-size: 5rem;"></i>

                            <h1 class="fw-bold">
                                ¡Pedido recibido!
                            </h1>

                            <p class="text-muted mt-3">
                                Tu pedido fue registrado correctamente.
                            </p>

                            <div class="bg-light rounded p-4 my-4">

                                <small class="text-muted d-block">
                                    Número de pedido
                                </small>

                                <strong class="fs-4">
                                    {{ $order->order_number }}
                                </strong>

                            </div>

                            <p class="text-muted">
                                Pronto recibirás información para
                                continuar con el pago de tu pedido.
                            </p>

                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                Seguir comprando
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

@endsection