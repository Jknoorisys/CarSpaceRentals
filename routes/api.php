<?php

use App\Http\Controllers\admin\CarBrandsController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DealerController;
use App\Http\Controllers\admin\LocationController as AdminLocationController;
use App\Http\Controllers\dealers\AuthController as DealersAuthController;
use App\Http\Controllers\dealers\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\dealers\ProfileController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// User Panel By Aaisha Shaikh
Route::prefix('user')->group( function () {
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/verifyOTP',[AuthController::class,'verifyOTP']);
    Route::post('/resendregOTP',[AuthController::class,'resendregOTP']);
    Route::post('/forgetpassword',[AuthController::class,'forgetpassword']);
    Route::post('/forgotPasswordValidate',[AuthController::class,'forgotPasswordValidate']);
    Route::post('/login',[AuthController::class,'login']);

    Route::group(['middleware' => 'jwt.verify'], function () {
    
        Route::post('/profile',[UserController::class,'profile']);
        Route::post('/logout',[AuthController::class,'logout']);
        
    });
});

// Admin Panel By Javeriya Kauser
Route::prefix('admin')->group(function () {

    Route::group(['middleware' => 'jwt.verify'], function () {

        // Dashboard
        Route::post('dashboard' , [DashboardController::class, 'dashboard']);

        // Manage Car Brands
        Route::post('get-all-brands' , [CarBrandsController::class, 'getCarBrands']);
        Route::post('add-brand' , [CarBrandsController::class, 'addCarBrand']); 
        Route::post('get-brand' , [CarBrandsController::class, 'getCarBrand']); 
        Route::post('edit-brand' , [CarBrandsController::class, 'editCarBrand']); 

        // Manage Customers
        Route::post('get-all-customers' , [CustomerController::class, 'getCustomers']);
        Route::post('change-customers-status' , [CustomerController::class, 'changeCustomerStatus']);
        Route::post('make-customer-admin' , [CustomerController::class, 'makeCustomerAdmin']);
        Route::post('customer-login-activity' , [CustomerController::class, 'getCustomerLoginActivity']);

        // Manage Dealers
        Route::post('get-all-dealers' , [DealerController::class, 'getDealers']);
        Route::post('change-dealer-status' , [DealerController::class, 'changeDealerstatus']);
        Route::post('make-dealer-admin' , [DealerController::class, 'makeDealerAdmin']);
        Route::post('dealer-login-activity' , [DealerController::class, 'getDealerLoginActivity']);
        Route::post('get-dealer-details' , [DealerController::class, 'getDealerDetails']);
        Route::post('get-dealer-cars' , [DealerController::class, 'getDealerCars']);
        Route::post('get-dealer-plots' , [DealerController::class, 'dealersBookedPlots']);

        // Manage Rental Locations
        Route::post('add-location' , [AdminLocationController::class, 'addLocation']); 
        Route::post('get-all-locations' , [AdminLocationController::class, 'getLocations']); 
        Route::post('get-location-details' , [AdminLocationController::class, 'getLocationDetails']); 
        Route::post('get-location' , [AdminLocationController::class, 'getLocation']); 
        Route::post('edit-location' , [AdminLocationController::class, 'updateLocation']); 
        Route::post('change-location-status' , [AdminLocationController::class, 'changeLocationStatus']);

    });
});

// Dealer Panel
Route::prefix('dealer')->group( function () {

    // By Aaisha Shaikh
    Route::post('/register',[DealersAuthController::class,'register']);
    Route::post('/verifyOTP',[DealersAuthController::class,'verifyOTP']);
    Route::post('/resendregOTP',[DealersAuthController::class,'resendregOTP']);
    Route::post('/forgetpassword',[DealersAuthController::class,'forgetpassword']);
    Route::post('/forgotPasswordValidate',[DealersAuthController::class,'forgotPasswordValidate']);
    Route::post('/login',[DealersAuthController::class,'login']);

    Route::group(['middleware' => 'jwt.verify'], function () {

    Route::post('getProfile',[ProfileController::class,'getProfile']);
    Route::post('UpdateProfile',[ProfileController::class,'UpdateProfile']);
    Route::post('UpdateProfileDetail',[ProfileController::class,'UpdateProfileDetail']);

    });

    // By Javeriya Kauser
    Route::post('get-all-locations' , [LocationController::class, 'getLocations']); 
    Route::post('get-location-details' , [LocationController::class, 'getLocationDetails']); 

});