<?php

use Illuminate\Support\Facades\Route;



Route::middleware(['lang'])->group(function () {
    require base_path('app/Modules/Authentication/Routes/authentication.php');
    require base_path('app/Modules/Projects/Routes/api.php');
    require base_path('app/Modules/Tasks/Routes/api.php');
    require base_path('app/Modules/Dashboard/Routes/api.php');
});

