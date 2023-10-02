<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\IpWhitelistController;
use App\Http\Controllers\v1\DepartmentController;
use App\Http\Controllers\v1\DepartmentTokenController;
use App\Http\Controllers\v1\BankTransferController;
use App\Http\Controllers\v1\HooksController;
use App\Http\Controllers\v1\AccountDetailController;
use App\Http\Controllers\v1\ProviderController;
use App\Http\Controllers\v1\WalletController;
use App\Http\Controllers\v1\NotificationController;

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

Route::post('create-department', [DepartmentController::class, 'createDepartment']);
Route::post('create-provider', [ProviderController::class, 'createProvider']);
Route::get('get-provider', [ProviderController::class, 'providerList']);
Route::get('create-token/{departmentId}', [DepartmentTokenController::class, 'generateAndStoreTokens']);
Route::post('add_ip/{departmentId}', [IpWhitelistController::class, 'addToWhitelist']);

Route::middleware(['key.auth:secret'])->group(function () {
    Route::post('initiate-transfer', [BankTransferController::class, 'transfer']);
    Route::post('create-account/individual', [AccountDetailController::class, 'createAccount']);  
    Route::post('hook/{walletId}', [HooksController::class, 'hooksData']);
    Route::get('all-wallet', [WalletController::class, 'allWallet']);
    Route::get('bank-list', [BankTransferController::class, 'bankList']);
    Route::post('enquiry', [BankTransferController::class, 'nameEnquiry']);
    Route::post('notification/email', [NotificationController::class, 'sendEmailNotification']);
    Route::post('notification/phone', [NotificationController::class, 'sendPhoneNotification']);

    
});


?>