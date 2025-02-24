<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\PermitRequestApplicationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ZoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/auth_user', function (Request $request) {
    return $request->user()->load(['roles', 'department']);
})->middleware('auth:api');

Route::middleware('auth:api')->name('api.')->group(function () {


    Route::group(['middleware' => ['role:staff']], function () {
        Route::controller(PermitController::class)->prefix('permits')->name('permits.')->group(function () {
            Route::get('/mypermit', 'myPermit')->name('myPermit');
        });
    });

    Route::group(['middleware' => ['role:admin']], function () {
        Route::controller(PermitController::class)->prefix('permits')->name('permits.')->group(function () {
            Route::get('/', 'index')->name('index');
        });

        Route::controller(DepartmentController::class)->prefix('departments')->name('departments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });


        Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });


        Route::controller(ZoneController::class)->prefix('zones')->name('zones.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });
    });

    Route::group(['middleware' => ['role:security-officer']], function () {
        Route::controller(PermitController::class)->prefix('permits')->name('permits.')->group(function () {
            Route::post('/validate', 'validate')->name('validate');
        });
    });


    Route::controller(PermitRequestApplicationController::class)->prefix('applications')->name('applications.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('role:admin');
        Route::get('/myapplication', 'myApplication')->name('my-application')->middleware('role:staff');
        Route::post('/myapplication', 'store')->name('store')->middleware('role:staff');
        Route::post('/{permit_request_application}/review', 'review')->name('review')->middleware('role:admin');

    });
});

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:api');
