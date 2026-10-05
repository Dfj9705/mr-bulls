<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $category = null;

    public string $sort = 'latest';

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => null],
        'sort' => ['except' => 'latest'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'category',
            'sort',
        ]);

        $this->sort = 'latest';

        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)

            ->when(
                filled($this->search),
                function ($query) {
                    $search = '%' . trim($this->search) . '%';

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', $search)
                            ->orWhere('sku', 'like', $search)
                            ->orWhere('short_description', 'like', $search);
                    });
                }
            )

            ->when(
                $this->category,
                fn($query) =>
                    $query->where('category_id', $this->category)
            );

        switch ($this->sort) {

            case 'price_asc':
                $products->orderBy('price');
                break;

            case 'price_desc':
                $products->orderByDesc('price');
                break;

            case 'name':
                $products->orderBy('name');
                break;

            default:
                $products->latest();
                break;
        }

        return view('livewire.shop.product-catalog', [
            'categories' => $categories,
            'products' => $products->paginate(12),
        ]);
    }
}