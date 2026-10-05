<div>

    @if($product->stock > 0)

        <div class="mb-4">

            <label class="form-label fw-semibold">
                Cantidad
            </label>

            <div
                class="input-group"
                style="max-width: 150px;"
            >

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    wire:click="decrease"
                >
                    −
                </button>

                <input
                    type="number"
                    class="form-control text-center"
                    wire:model.blur="quantity"
                    min="1"
                    max="{{ $product->stock }}"
                >

                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    wire:click="increase"
                >
                    +
                </button>

            </div>

        </div>


        <button
            type="button"
            class="btn btn-primary btn-lg w-100"
            wire:click="add"
            wire:loading.attr="disabled"
            wire:target="add"
        >

            <span wire:loading.remove wire:target="add">
                <i class="bx bx-cart me-2"></i>
                Agregar al carrito
            </span>

            <span wire:loading wire:target="add">
                Agregando...
            </span>

        </button>

    @else

        <button
            type="button"
            class="btn btn-secondary btn-lg w-100"
            disabled
        >
            Producto agotado
        </button>

    @endif

</div>