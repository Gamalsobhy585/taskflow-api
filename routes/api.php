<?php

use Illuminate\Support\Facades\Route;



Route::middleware(['lang'])->group(function () {
    require base_path('app/Modules/Authentication/Routes/authentication.php');
    require base_path('app/Modules/Projects/Routes/api.php');
});

