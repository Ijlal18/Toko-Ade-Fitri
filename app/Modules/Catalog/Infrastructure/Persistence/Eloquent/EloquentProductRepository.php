<?php

namespace App\Modules\Catalog\Infrastructure\Persistence\Eloquent;

use App\Modules\Catalog\Domain\Repositories\ProductRepositoryInterface;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return Product::where('barcode', $barcode)->first();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);

        $product->update($data);

        return $product->refresh();
    }

    public function delete(int $id): bool
    {
        $product = Product::findOrFail($id);

        return (bool) $product->delete();
    }

    public function paginate(int $perPage = 15)
    {
        return Product::with('category')
            ->latest()
            ->paginate($perPage);
    }
}
