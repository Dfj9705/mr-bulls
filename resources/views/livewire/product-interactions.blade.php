<div class="mt-4">

    <div class="d-flex align-items-center gap-3 mb-4">
        @auth
            @if(auth()->user()->hasVerifiedEmail())
                <button type="button" wire:click="toggleLike" wire:loading.attr="disabled" wire:target="toggleLike"
                    class="btn {{ $hasLiked ? 'btn-danger' : 'btn-outline-danger' }}">
                    <i class="bx {{ $hasLiked ? 'bxs-heart' : 'bx-heart' }} me-1"></i>
                    {{ $hasLiked ? 'Te gusta' : 'Me gusta' }}
                </button>
            @else
                <span class="text-muted small">
                    Verifica tu correo para dar me gusta.
                </span>
            @endif
        @else
            <span class="text-muted small">
                Inicia sesión para dar me gusta.
            </span>
        @endauth

        <span class="fw-semibold">
            {{ number_format($likesCount) }} me gusta
        </span>
    </div>

    @error('interaction')
        <div class="alert alert-warning">{{ $message }}</div>
    @enderror

    <h5 class="fw-bold mb-3">
        Comentarios ({{ $this->product->approvedComments()->count() }})
    </h5>

    @auth
        @if(auth()->user()->hasVerifiedEmail())
            <form wire:submit="submitComment" class="mb-4">
                <div class="mb-3">
                    <textarea wire:model="comment" class="form-control @error('comment') is-invalid @enderror" rows="3"
                        maxlength="1000" placeholder="¿Qué opinas de este producto?"></textarea>

                    @error('comment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="submitComment">
                    Publicar comentario
                </button>
            </form>
        @else
            <div class="alert alert-info">
                Debes verificar tu correo para publicar comentarios.
                <a href="{{ route('verification.notice') }}">
                    Verificar correo
                </a>
            </div>
        @endif
    @else
        <div class="alert alert-light border">
            Inicia sesión para comentar este producto.
        </div>
    @endauth

    @if(session()->has('comment_success'))
        <div class="alert alert-success">
            {{ session('comment_success') }}
        </div>
    @endif

    @forelse($comments as $item)
        <div class="border-bottom py-3" wire:key="comment-{{ $item->id }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>{{ $item->user?->name ?? 'Cliente' }}</strong>
                <small class="text-muted">
                    {{ $item->created_at->format('d/m/Y') }}
                </small>
            </div>

            <p class="mb-0" style="white-space: pre-wrap;">{{ $item->content }}</p>
        </div>
    @empty
        <p class="text-muted">
            Este producto todavía no tiene comentarios publicados.
        </p>
    @endforelse

    <div class="mt-4">
        {{ $comments->links() }}
    </div>

</div>