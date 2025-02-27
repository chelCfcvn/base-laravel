<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PaymentTermController;
use App\Http\Controllers\Api\StatisticController;
use App\Http\Controllers\Api\UploadImageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\CheckUserActive;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your Api!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['as' => 'auth.', 'prefix' => 'auth'], function (): void {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'currentLoginUser'])->name('currentLoginUser');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('changePassword');
});

Route::group(['middleware' => ['auth:user', CheckUserActive::class]], function (): void {
    Route::post('/upload-image', [UploadImageController::class, 'upload'])->name('uploadImage');
    Route::get('/master-data', [MasterDataController::class, 'show'])->name('masterData');

    Route::group(['as' => 'customers.', 'prefix' => 'customers'], function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [CustomerController::class, 'edit'])->name('edit');
        Route::post('/store', [CustomerController::class, 'store'])->name('store');
        Route::post('/update', [CustomerController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [CustomerController::class, 'delete'])->name('delete');
    });

    Route::group(['as' => 'payment-terms.', 'prefix' => 'payment-terms'], function () {
        Route::get('/{customerId}', [PaymentTermController::class, 'index'])->name('index');
        Route::post('/store', [PaymentTermController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [PaymentTermController::class, 'edit'])->name('edit');
        Route::post('/update', [PaymentTermController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [PaymentTermController::class, 'delete'])->name('delete');
        Route::post('/update-status', [PaymentTermController::class, 'updateStatus'])->name('update_status');
    });

    Route::group(['as' => 'statistic.', 'prefix' => 'statistics'], function () {
        Route::get('/', [StatisticController::class, 'index'])->name('index');
        Route::get('/profit-amount', [StatisticController::class, 'getProfitAmount'])->name('profit_amount');
    });

    Route::group(['as' => 'messages.', 'prefix' => 'messages'], function (): void {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::post('/', [MessageController::class, 'store'])->name('store');
    });

    Route::resource('users', UserController::class);
    Route::post('/users/{user}/update-status', [UserController::class, 'updateStatus'])->name('update_status_user');
});
