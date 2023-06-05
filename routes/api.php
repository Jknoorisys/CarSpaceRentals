<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Admin
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\CarBrandsController;
use App\Http\Controllers\admin\CustomerController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\DealerController;
use App\Http\Controllers\admin\FeaturedcarPriceController;
use App\Http\Controllers\admin\LocationController as AdminLocationController;
use App\Http\Controllers\admin\FeaturedCarController;
use App\Http\Controllers\admin\LocationLineController;
// Dealer
use App\Http\Controllers\dealers\AuthController as DealersAuthController;
use App\Http\Controllers\dealers\LocationController;
use App\Http\Controllers\dealers\ProfileController;
use App\Http\Controllers\dealers\CarController;
use App\Http\Controllers\dealers\FeaturedCarController as DealersFeaturedCarController;
use App\Http\Controllers\dealers\PaymentPlotController;
use App\Http\Controllers\dealers\PaymentFcarController;
use App\Http\Controllers\stripe\PlotBookingController;
// Users
use App\Http\Controllers\users\AuthController;
use App\Http\Controllers\users\ProfileController as UserProfileController;
use App\Http\Controllers\users\CarController as UserCarController;
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

    //Authentication
    Route::post('register',[AuthController::class,'register']);
    Route::post('verifyOTP',[AuthController::class,'verifyOTP']);
    Route::post('resendregOTP',[AuthController::class,'resendregOTP']);
    Route::post('forgetpassword',[AuthController::class,'forgetpassword']);
    Route::post('forgotPasswordValidate',[AuthController::class,'forgotPasswordValidate']);
    Route::post('login',[AuthController::class,'login']);

    //Cars
    Route::post('getCarListByname',[UserCarController::class,'getCarListByname']);
    Route::post('CarList',[UserCarController::class,'CarList']);
    Route::post('carFilter',[UserCarController::class,'carFilter']);
    Route::post('getCarBrands',[UserCarController::class,'getCarBrands']);
    Route::post('featuredCarList',[UserCarController::class,'featuredCarList']);
    Route::post('CarDetails',[UserCarController::class,'CarDetails']);
    Route::post('Car_details_and_featured_car',[UserCarController::class,'Car_details_and_featured_car']);
    Route::post('other_car_from_same_dealer',[UserCarController::class,'other_car_from_same_dealer']);

    Route::group(['middleware' => 'jwt.verify'], function () {
    
    //Profile
    Route::post('getProfile',[UserProfileController::class,'getProfile']);
    Route::post('UpdateProfile',[UserProfileController::class,'UpdateProfile']);
    Route::post('UpdateProfileDetail',[UserProfileController::class,'UpdateProfileDetail']);

    Route::post('logout',[AuthController::class,'logout']);
   
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

        // Manage Location Lines
        Route::post('add-lane' , [LocationLineController::class, 'addLocationLine']); 
        Route::post('change-lane-status' , [LocationLineController::class, 'changeLaneStatus']);
        Route::post('get-all-lanes' , [LocationLineController::class, 'getAllLines']); 
        Route::post('get-lane-details' , [LocationLineController::class, 'getLineDetails']); 
        Route::post('edit-plot' , [LocationLineController::class, 'editPlotDetails']); 

        // Manage Admins
        Route::post('get-all-admins' , [AdminController::class, 'getAllAdmins']);
        Route::post('change-admin-status' , [AdminController::class, 'changeAdminStatus']);
        Route::post('admin-login-activity' , [AdminController::class, 'getAdminLoginActivity']);
        Route::post('get-admin-action-history' , [AdminController::class, 'getAdminActionHistory']);

        // Manage Featured Car Price
        Route::post('get-featured-car-price' , [FeaturedcarPriceController::class, 'getFeaturedcarPrice']);
        Route::post('edit-featured-car-price' , [FeaturedcarPriceController::class, 'editFeaturedcarPrice']);

        //Manage Featured Car By Aaisha Shaikh
        Route::post('getFeaturedCar' , [FeaturedCarController::class, 'getFeaturedCar']);
        Route::post('GetDetailsFeaturedCar' , [FeaturedCarController::class, 'GetDetailsFeaturedCar']);

        //Transaction History By Aaisha Shaikh
        Route::post('TransactionHistory', [FeaturedCarController::class, 'TransactionHistory']);
    });
});

// Dealer Panel
Route::prefix('dealer')->group( function () {

    // Authentication By Aaisha Shaikh
    Route::post('register',[DealersAuthController::class,'register']);
    Route::post('verifyOTP',[DealersAuthController::class,'verifyOTP']);
    Route::post('resendregOTP',[DealersAuthController::class,'resendregOTP']);
    Route::post('forgetpassword',[DealersAuthController::class,'forgetpassword']);
    Route::post('forgotPasswordValidate',[DealersAuthController::class,'forgotPasswordValidate']);
    Route::post('login',[DealersAuthController::class,'login']);

    Route::group(['middleware' => 'jwt.verify'], function () {

        //Profile By Aaisha Shaikh
        Route::post('getProfile',[ProfileController::class,'getProfile']);
        Route::post('UpdateProfile',[ProfileController::class,'UpdateProfile']);
        Route::post('UpdateProfileDetail',[ProfileController::class,'UpdateProfileDetail']);

        //Cars By Aaisha Shaikh
        Route::post('addCar',[CarController::class,'addCar']);
        Route::post('getCarbyID',[CarController::class,'getCarbyID']);
        Route::post('editCar',[CarController::class,'editCar']);
        Route::post('logout',[AuthController::class,'logout']);
    
        // Dealer Locations Module By Javeriya Kauser
        Route::post('get-all-locations' , [LocationController::class, 'getLocations']); 
        Route::post('get-location-details' , [LocationController::class, 'getLocationDetails']); 
        Route::post('get-all-car-brands' , [CarController::class, 'getCarBrands']); 
    
        Route::post('get-locations-list' , [LocationController::class, 'getLocationList']); 
        Route::post('get-lanes-based-on-location' , [LocationController::class, 'getLinesBasedOnLocations']); 
        Route::post('get-available-plots' , [LocationController::class, 'getAvailablePlotsByDate']); 
        Route::post('get-selected-plots' , [LocationController::class, 'getSelectedPlots']); 
    
        Route::post('get-dealer-cars' , [LocationController::class, 'getDealerCars']); 
        Route::post('get-dealer-plots' , [LocationController::class, 'getDealerPlots']); 
        Route::post('get-dealer-locations' , [LocationController::class, 'getDealerLocations']); 
        Route::post('get-lanes-based-on-location' , [LocationController::class, 'getDealerLanesBasedOnLocation']);
        Route::post('get-plots-based-on-location' , [LocationController::class, 'getPlotsBasedOnLocation']); 
        Route::post('get-dealer-all-plots-list' , [LocationController::class, 'getDealerAllPlotsList']); 

        Route::post('assign-car' , [CarController::class, 'assignCarToPlot']);
        Route::post('unassign-car' , [CarController::class, 'unassignCarFromPlot']);

        //Orange Payment by Aaisha Shaikh
        Route::post('orange_payment_for_plot_booking',[PaymentPlotController::class,'orange_payment_for_plot_booking']);
        Route::post('orange_payment_for_car_booking',[PaymentFcarController::class,'orange_payment_for_car_booking']);
        Route::post('orange_payment_plot_success',[PaymentPlotController::class,'orange_payment_plot_success']);

        //Featured Car by Aaisha Shaikh
        Route::post('getFeaturedcarPrice',[DealersFeaturedCarController::class,'getFeaturedcarPrice']);

        
        // Stripe Payment By Javeriya
        Route::prefix('stripe')->group( function () {
            Route::post('plot-booking',[PlotBookingController::class,'plotPayment']);
            Route::post('plot-booking-successful',[PlotBookingController::class,'plotPaymentSuccessfull']);
            Route::post('plot-booking-failed',[PlotBookingController::class,'plotPaymentFailed']);

        });

    });
});