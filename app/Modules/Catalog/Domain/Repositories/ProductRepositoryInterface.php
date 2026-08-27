<?php

namespace App\Modules\Catalog\Domain\Repositories;

use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findByBarcode(string $barcode): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    public function paginate(int $perPage = 15);
}
