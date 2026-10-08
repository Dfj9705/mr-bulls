<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductComment;
use App\Models\ProductLike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\RateLimiter;

class ProductInteractions extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';
    public Product $product;

    public string $comment = '';

    public function toggleLike(): void
    {
        $user = Auth::user();

        if (!$user || !$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'interaction' => 'Debes iniciar sesión y verificar tu correo para dar me gusta.',
            ]);
        }

        $key = 'product-like:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 20)) {
            throw ValidationException::withMessages([
                'interaction' => 'Has realizado demasiadas acciones. Intenta nuevamente en un minuto.',
            ]);
        }

        RateLimiter::hit($key, 60);

        $existing = ProductLike::query()
            ->where('product_id', $this->product->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ProductLike::query()->firstOrCreate([
                'product_id' => $this->product->id,
                'user_id' => $user->id,
            ]);
        }
    }

    public function submitComment(): void
    {
        $user = Auth::user();

        if (!$user || !$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'interaction' => 'Debes iniciar sesión y verificar tu correo para comentar.',
            ]);
        }

        $this->validate([
            'comment' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'comment.required' => 'Escribe un comentario.',
            'comment.min' => 'El comentario debe tener al menos 3 caracteres.',
            'comment.max' => 'El comentario no puede superar los 1000 caracteres.',
        ]);

        $key = 'product-comment:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'interaction' => 'Has enviado demasiados comentarios. Espera unos minutos antes de intentar nuevamente.',
            ]);
        }

        RateLimiter::hit($key, 300);

        ProductComment::create([
            'product_id' => $this->product->id,
            'user_id' => $user->id,
            'content' => trim($this->comment),
        ]);

        $this->reset('comment');

        session()->flash(
            'comment_success',
            'Tu comentario fue enviado y está pendiente de aprobación.'
        );
    }

    public function render()
    {
        $likesCount = $this->product->likes()->count();

        $hasLiked = Auth::check()
            && $this->product->likes()
                ->where('user_id', Auth::id())
                ->exists();

        $comments = $this->product->approvedComments()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return view('livewire.product-interactions', [
            'likesCount' => $likesCount,
            'hasLiked' => $hasLiked,
            'comments' => $comments,
        ]);
    }
}
