<?php

use Illuminate\Support\Facades\Route;
use Modules\Deliveries\Http\Controllers\DeliveriesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('deliveries', DeliveriesController::class)->names('deliveries');
});
