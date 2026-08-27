<?php

use App\Modules\Catalog\Infrastructure\Providers\CatalogServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    CatalogServiceProvider::class,
];
