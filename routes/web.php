<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Test');
});

Route::get('/test', function () {
    return inertia('Test');
});
