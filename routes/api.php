<?php

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrandController;
use App\Http\Resources\User\UserResource;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\User\Order\OrderController;
use App\Http\Controllers\Admin\OrderReportController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\User\Cart\GuestCartController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Seller\MonthlyTargetController;
use App\Http\Controllers\Seller\Product\ProductController;
use App\Http\Controllers\Seller\Product\VariantController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\User\Order\OrderReviewController;
use App\Http\Controllers\Admin\CompanyBrandBannerController;
use App\Http\Controllers\Fullfillment\FullfillmentController;
use App\Http\Controllers\User\Order\CustomerReportController;





// Route for authenticating users using session
Route::post('/user/signup',[UserController::class,'signup']);
Route::post('/user/login',[UserController::class,'login']);


//proudcts global routes

// guest cart routes
Route::post('/guest-cart', [GuestCartController::class, 'details']);

Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);
Route::get('/products/trending', [ProductController::class, 'trending']);
Route::get('/products',[ProductController::class,'index']);//this route will return all the products
Route::get('/products/search',[ProductController::class,'search']);//this route will return all the products
Route::get('/products/recomended/{cId}',[ProductController::class,'recomended']);//this route will return all the products
Route::get('/products/{id}',[ProductController::class,'showProduct']);//this route will return all the product details

Route::get('/brands',[BrandController::class,'index']);//this route will return all brands
Route::get('/brands/trending',[BrandController::class,'trending']);//this route will return all brands




Route::middleware(['auth:sanctum'])->group(function () {
    //controller which will send user profile data
    Route::get('/user/profile',[ProfileController::class,'show']);

    // Users(company staff) related routes
    Route::controller(UserController::class)->group(function () {
            Route::get('/user/logout','logout');
            Route::get('/all/users','getAllUsers');
            Route::get('/users/{id}','getUserById');
            Route::post('/add/user','createUser');
            Route::delete('/delete/user/{id}','deleteUser');
            Route::post('/update/user','updateUser');
            Route::post('/update/user/password','updatePassword');
        });

        Route::controller(RoleController::class)->group(function () {
            Route::get('/all/roles','getAllRoles');
        });

        // Fullfillment locations related routes
        Route::controller(FullfillmentController::class)->group(function (){

            Route::get('/all/fullfillment-locations','GetAllFullfillmentLocations');

            Route::post('/add/country','AddCountry');
            Route::get('/edit/country/{id}','EditCountry');
            Route::post('/update/country','UpdateCountry');

            // Route::post('/all/country','AllCountry');
            Route::delete('/delete/country/{country}','DeleteCountry');

            Route::get('/all/city','AllCity');
            Route::post('/add/city','AddCity');
            Route::get('/edit/city/{id}','EditCity');
            Route::delete('/delete/city/{id}','DeleteCity');
            Route::post('/update/city','UpdateCity');


            Route::get('/all/state','AllState');
            Route::post('/add/state','AddState');
            Route::delete('/delete/state/{id}','DeleteState');
            Route::get('/edit/state/{id}','EditState');
            Route::post('/update/state','UpdateState');

        });

        Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
            Route::get('/company-brand-banner', [CompanyBrandBannerController::class, 'show']);
            Route::post('/company-brand-banner', [CompanyBrandBannerController::class, 'update']);
            Route::get('/all-orders', [OrderController::class, 'getAllOrders']);
            Route::post('/orders/report', [OrderReportController::class, 'download'])->name('orders.report.download');
            Route::post('/orders/{id}/report', [OrderReportController::class, 'downloadSingleOrder']);

            /// admin dashbaord controller
            Route::controller(AdminDashboardController::class)->group(function () {
                Route::get('/dashboard', 'index')->name('dashboard');
                // Route::get('/customer-growth', 'customerGrowthStats')->name('customer-growth');

            });
        });

        Route::apiResource('/addresses', AddressController::class);

        //routes for attributes
        Route::controller(AttributeController::class)->group(function() {
            Route::get('/categories/{category}/attributes', 'index');
            Route::get('/attributes', 'allAttributes');
            Route::post('/attributes', 'store');
            Route::get('/attributes/{attribute}', 'show');
            Route::put('/attributes/{attribute}', 'update');
            Route::delete('/attributes/{attribute}', 'destroy');
        });

        Route::prefix('admin/customer-reports')->group(function () {
            // Create a report
            Route::get('/', [CustomerReportController::class, 'index']);

            // View single report (auth: customer or admin)
            Route::get('/{id}', [CustomerReportController::class, 'update']);


            Route::patch('/{id}/resolved', [CustomerReportController::class, 'markResolved']);
            Route::patch('/{id}/in-progress', [CustomerReportController::class, 'markInProgress']);
        });


        // routes for products
        Route::prefix('seller/products')->group(function () {
            // All product routes handled by ProductController
            Route::controller(ProductController::class)->group(function () {
                Route::get('/',             'index');
                Route::post('/',            'store');
                Route::get('/{id}',            'show');
                Route::post('{id}',          'update');
                Route::delete('{id}',       'destroy');
                Route::post('{id}/receive-fba',    'receiveFBAStock');
                Route::post('{id}/report-issues',  'reportFBAIssues');
            });
        });
        Route::post('/variant-inventory/update', [VariantController::class, 'updateVariantInventory']);

        // order reviews routes
        Route::controller(OrderReviewController::class)->prefix('/order/reviews')->group(function(){
            Route::get('${id}','index');
            Route::post('','store');
        });

        Route::prefix('customer-reports')->group(function () {
            // Create a report
            Route::post('/', [CustomerReportController::class, 'store']);

            // View single report (auth: customer or admin)
            Route::get('/{id}', [CustomerReportController::class, 'show']);
        });


        Route::controller(SellerOrderController::class)->prefix('/seller/orders')->group(function(){
            Route::get('','index');
            Route::get('/recent','recent');
            Route::get('/{id}','show');
            Route::post('/mark-order-as-delivered/{id}','markAsDelivered');
            Route::post('/order-report/{orderId}','downloadOrderReport');
        });

        // seller routes for monthly targets

         Route::controller(SellerOrderController::class)->prefix('seller')->group(function(){
            // Create or update a monthly target
            Route::post('/monthly-targets', [MonthlyTargetController::class, 'upsert']);
            Route::get('/monthly-targets/all', [MonthlyTargetController::class, 'allTargetsWithRevenue']);
            // Get the monthly target for a given month (passed as query param)
            Route::get('/monthly-targets/{id}', [MonthlyTargetController::class, 'show']);

        });

        //seller dashboard routes
        Route::controller(SellerDashboardController::class)->prefix('/seller/dashboard')->group(function(){
            Route::get('/orders-count','sellerOrdersCount');
            Route::get('/monthly-sales','getMonthlySales');
            Route::get('/total-sales','getTotalRevenue');
        });

        //sellers routes
        Route::controller(SellerController::class)->prefix('sellers')->group(function () {
            Route::get('/', 'index');                       // GET /sellers
            Route::post('/', 'store');                      // POST /sellers
            Route::get('{id}', 'show');                 // GET /sellers/{seller}
            Route::put('{id}', 'update');               // PUT /sellers/{seller}
            Route::patch('{id}', 'update');             // PATCH /sellers/{seller}
            Route::delete('{id}', 'destroy');           // DELETE /sellers/{seller}
            // routes/web.php or routes/api.php

            // routes for changing status of sellers
            Route::post('/{seller}/approve', 'approve');
            Route::post('/{seller}/reject', 'reject');
            Route::post('/{seller}/block', 'block');

            //
        });

        // Cart routes
        Route::apiResource('/carts', CartController::class);
        Route::post('/carts/sync', [CartController::class,'syncGuestCartItems']);

        // wishlist routes
        Route::apiResource('/wishlists', WishlistController::class);

        // orders routes
        Route::apiResource('/orders', OrderController::class);
    });


    // category related routes
    Route::get('/categories/with-children', [CategoryController::class, 'categoriesWithChildren']);
    Route::get('/categories/trending', [CategoryController::class, 'trending']);
    Route::apiResource('categories', CategoryController::class)->except(['update']);
    // Add your custom POST update route:
    Route::post('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);






//




