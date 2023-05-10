<?php

use App\Http\Controllers\admin\CarBrandsController;
use App\Http\Controllers\admin\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/userregister',[AuthController::class,'register']);
Route::post('/userlogin',[AuthController::class,'login']);

Route::group(['middleware' => 'jwt.verify'], function () {
    
    Route::post('/userprofile',[AuthController::class,'profile']);

});

// Admin Panel By Javeriya Kauser
Route::prefix('admin')->group(function () {
    // Manage Car Brands
   Route::post('get-all-brands' , [CarBrandsController::class, 'getCarBrands']);
   Route::post('add-brand' , [CarBrandsController::class, 'addCarBrand']); 
   Route::post('get-brand' , [CarBrandsController::class, 'getCarBrand']); 
   Route::post('edit-brand' , [CarBrandsController::class, 'editCarBrand']); 

    // Manage Customers
    Route::post('get-all-customers' , [CustomerController::class, 'getCustomers']);
    Route::post('change-customers-status' , [CustomerController::class, 'changeCustomerStatus']);
    Route::post('make-customer-admin' , [CustomerController::class, 'makeCustomerAdmin']);

});