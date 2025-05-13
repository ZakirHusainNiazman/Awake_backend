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
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\User\Cart\GuestCartController;
use App\Http\Controllers\Seller\Product\ProductController;
use App\Http\Controllers\Seller\Product\VariantController;
use App\Http\Controllers\Fullfillment\FullfillmentController;





// Route for authenticating users using session
Route::post('/user/signup',[UserController::class,'signup']);
Route::post('/user/login',[UserController::class,'login']);


//proudcts global routes

// guest cart routes
Route::post('/guest-cart', [GuestCartController::class, 'details']);

Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);
Route::get('/products/trending', [ProductController::class, 'trending']);
Route::get('/products',[ProductController::class,'index']);//this route will return all the products
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

        Route::controller(SellerOrderController::class)->prefix('/seller/orders')->group(function(){
            Route::get('','index');
            Route::get('/{id}','show');
            Route::get('/order-report/{orderId}','downloadOrderReport');

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
    Route::apiResource('categories', CategoryController::class);
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show']);






//




