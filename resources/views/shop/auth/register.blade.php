@extends('layouts.shop')

@section('title', 'Crear cuenta | Mr Bulls')

@section('content')

<section class="py-5">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-8 col-lg-5">

                <div class="card border-0 shadow-sm">

                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">

                            <h2 class="fw-bold">
                                Crear cuenta
                            </h2>

                            <p class="text-muted mb-0">
                                Regístrate para administrar tus pedidos
                                y direcciones.
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
                            action="{{ route('register.store') }}"
                        >

                            @csrf


                            <div class="mb-3">

                                <label
                                    for="name"
                                    class="form-label"
                                >
                                    Nombre completo
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="form-control"
                                    required
                                    autofocus
                                    autocomplete="name"
                                >

                            </div>


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
                                    autocomplete="new-password"
                                >

                                <small class="text-muted">
                                    Mínimo 8 caracteres.
                                </small>

                            </div>


                            <div class="mb-4">

                                <label
                                    for="password_confirmation"
                                    class="form-label"
                                >
                                    Confirmar contraseña
                                </label>

                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    required
                                    autocomplete="new-password"
                                >

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary btn-lg w-100"
                            >
                                Crear cuenta
                            </button>

                        </form>


                        <div class="text-center mt-4">

                            <span class="text-muted">
                                ¿Ya tienes una cuenta?
                            </span>

                            <a
                                href="{{ route('login') }}"
                                class="text-decoration-none fw-semibold"
                            >
                                Iniciar sesión
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection