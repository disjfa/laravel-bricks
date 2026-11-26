<?php

use App\Providers\AppServiceProvider;
use App\Providers\BrickServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\MenuServiceProvider;

return [
    AppServiceProvider::class,
    BrickServiceProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
    MenuServiceProvider::class,
];
