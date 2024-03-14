<?php

// use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\ClientAuthController;
// use App\Http\Controllers\PostController;
// use App\Http\Controllers\WorkerAuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\WorkerAuthController;
// use App\Http\Controllers\{AdminController, WorkerAuthController, ClientAuthController, PostController};
use App\Http\Controllers\ClientOrderController;
use App\Http\Controllers\AdminDashboard\PostStatusController;
use App\Http\Controllers\AdminDashboard\AdminNotificationController;











/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['DbBackup'])->prefix('auth')->group(function () {
    // Admin routes
    Route::group(['prefix' => 'admin'], function () {
        Route::post('/login', [AdminController::class, 'login']);
        Route::post('/register', [AdminController::class, 'register']);
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::post('/refresh', [AdminController::class, 'refresh']);
        Route::get('/user-profile', [AdminController::class, 'userProfile']);
    });

    // Worker routes
    Route::group(['prefix' => 'worker'], function () {
        Route::post('/login', [WorkerAuthController::class, 'login']);
        Route::post('/register', [WorkerAuthController::class, 'register']);
        Route::post('/logout', [WorkerAuthController::class, 'logout']);
        Route::post('/refresh', [WorkerAuthController::class, 'refresh']);
        Route::get('/user-profile', [WorkerAuthController::class, 'userProfile']);
        Route::get('/verify-email/{token}', [WorkerAuthController::class, 'verifyEmail']);
    });

    // Client routes
    Route::group(['prefix' => 'client'], function () {
        Route::post('/login', [ClientAuthController::class, 'login']);
        Route::post('/register', [ClientAuthController::class, 'register']);
        Route::post('/logout', [ClientAuthController::class, 'logout']);
        Route::post('/refresh', [ClientAuthController::class, 'refresh']);
        Route::get('/user-profile', [ClientAuthController::class, 'userProfile']);
    });
});

Route::get('/unauthorized', function () {
    return response()->json([
        "message" => "Unauthorized"
    ], 401);
})->name('login');


Route::prefix('admin')->group(function () {
    Route::controller(PostStatusController::class)->prefix('/post')->group(function () {
        Route::post('/status', 'changeStatus');
    });
    Route::controller(AdminNotificationController::class)
        ->middleware('auth:admin')
        ->prefix('admin/notifications')->group(function () {
            Route::get('/all', 'index');
            Route::get('/unread', 'unread');
            Route::post('/markReadAll', 'markReadAll');
            Route::delete('/deleteAll', 'deleteAll');
            Route::delete('/delete/{id}', 'delete');
        });
});
Route::controller(PostController::class)->prefix('worker/post')->group(function () {
    Route::post('/add', 'store')->middleware('auth:worker');
    Route::get('/show', 'index')->middleware('auth:admin');
    Route::get('/approved', 'approved');
});

Route::prefix('client')->group(function () {
    Route::controller(ClientOrderController::class)->prefix('/order')->group(function () {
        Route::post('/request', 'addOrder')->middleware('auth:client');
        Route::get('/approved', 'approvedOrders')->middleware('auth:client');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::get('/pay/{serviceId}', 'pay');
    });
});
