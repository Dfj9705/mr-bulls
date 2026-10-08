@extends('layouts.shop')

@section('title', 'Verificar correo | Mr Bulls')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4 text-center">

                            <i class="bx bx-envelope fs-1 text-primary"></i>

                            <h3 class="fw-bold mt-3">
                                Verifica tu correo
                            </h3>

                            <p class="text-muted mt-3">
                                Enviamos un enlace de confirmación a:
                            </p>

                            <p class="fw-semibold">
                                {{ auth()->user()->email }}
                            </p>

                            <p class="text-muted">
                                Revisa tu bandeja de entrada y
                                haz clic en el enlace para
                                verificar tu cuenta.
                            </p>

                            @if(session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf

                                <button type="submit" class="btn btn-primary w-100">
                                    Reenviar correo de verificación
                                </button>
                            </form>

                            <a href="{{ route('home') }}" class="btn btn-link mt-3">
                                Volver a la tienda
                            </a>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection