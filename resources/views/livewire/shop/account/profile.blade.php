<div>
    <section class="bg-white border-bottom">
        <div class="container py-5">
            <span class="text-muted">
                Mi cuenta
            </span>

            <h1 class="fw-bold mb-1">
                Mi perfil
            </h1>

            <p class="text-muted mb-0">
                Administra la información de tu cuenta.
            </p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-8">

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit="save">

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-4">
                                    Información personal
                                </h4>

                                <div class="mb-3">
                                    <label
                                        for="name"
                                        class="form-label"
                                    >
                                        Nombre
                                    </label>

                                    <input
                                        type="text"
                                        id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name"
                                    >

                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="email"
                                        class="form-label"
                                    >
                                        Correo electrónico
                                    </label>

                                    <input
                                        type="email"
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        wire:model="email"
                                    >

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-4">

                                <h4 class="fw-bold mb-2">
                                    Cambiar contraseña
                                </h4>

                                <p class="text-muted mb-4">
                                    Déjala en blanco si no deseas cambiarla.
                                </p>

                                <div class="mb-3">
                                    <label
                                        for="password"
                                        class="form-label"
                                    >
                                        Nueva contraseña
                                    </label>

                                    <input
                                        type="password"
                                        id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        wire:model="password"
                                        autocomplete="new-password"
                                    >

                                    @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        for="password_confirmation"
                                        class="form-label"
                                    >
                                        Confirmar contraseña
                                    </label>

                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        class="form-control"
                                        wire:model="password_confirmation"
                                        autocomplete="new-password"
                                    >
                                </div>

                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                wire:loading.attr="disabled"
                                wire:target="save"
                            >
                                <span
                                    wire:loading.remove
                                    wire:target="save"
                                >
                                    Guardar cambios
                                </span>

                                <span
                                    wire:loading
                                    wire:target="save"
                                >
                                    Guardando...
                                </span>
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </section>
</div>