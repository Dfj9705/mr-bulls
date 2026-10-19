@extends('layouts.shop')

@section('title', 'Mi monedero | Mr Bulls')

@section('content')
    <div class="container py-4 py-lg-5">

        <div class="mb-4">
            <h1 class="h3 fw-bold mb-1">
                Mi monedero
            </h1>

            <p class="text-muted mb-0">
                Consulta tus recompensas y movimientos.
            </p>
        </div>

        <livewire:shop.wallet-dashboard />

    </div>
@endsection