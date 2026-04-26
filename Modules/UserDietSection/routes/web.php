<?php

use Illuminate\Support\Facades\Route;
use Modules\UserDietSection\Http\Controllers\UserDietSectionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('userdietsections', UserDietSectionController::class)->names('userdietsection');
});
