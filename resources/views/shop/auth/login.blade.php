@extends('layouts.shop')

@section('title', 'Iniciar sesión | Mr Bulls')

@section('content')

<section class="py-5">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-8 col-lg-5">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">

                            <h2 class="fw-bold">
                                Iniciar sesión
                            </h2>

                            <p class="text-muted mb-0">
                                Ingresa a tu cuenta de Mr Bulls.
                            </p>

                        </div>


                        @if($errors->any())

                            <div class="alert alert-danger">

                                @foreach($errors->all() as $error)

                                    <div>
                                        {{ $error }}
                                    </div>

                                @endforeach

                            </div>

                        @endif


                        <form
                            method="POST"
                            action="{{ route('login.store') }}"
                        >

                            @csrf


                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    Correo electrónico
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="form-control"
                                    required
                                    autofocus
                                    autocomplete="email"
                                >

                            </div>


                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label"
                                >
                                    Contraseña
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    required
                                    autocomplete="current-password"
                                >

                            </div>


                            <div class="form-check mb-4">

                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="remember"
                                    name="remember"
                                    value="1"
                                >

                                <label
                                    class="form-check-label"
                                    for="remember"
                                >
                                    Recordarme
                                </label>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100"
                            >
                                Iniciar sesión
                            </button>

                        </form>


                        <div class="text-center mt-4">

                            <span class="text-muted">
                                ¿No tienes una cuenta?
                            </span>

                            <a
                                href="{{ route('register') }}"
                                class="text-decoration-none fw-semibold"
                            >
                                Crear cuenta
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection