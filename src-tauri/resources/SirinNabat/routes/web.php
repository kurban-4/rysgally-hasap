<?php


use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\WholesaleStorageController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return auth()->check() ? redirect()->route('welcome') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});


Route::middleware(['auth'])->group(function () {
    
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    
    Route::prefix('admin')->group(function () {
        
        
        Route::get('/welcome', function () { return view('welcome'); })->name('welcome');
        
        
        Route::get('/home', function() { return redirect()->route('welcome'); });

        
        Route::controller(MedicineController::class)->prefix('medicine')->name('medicine.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/search', 'search')->name('search'); 
            Route::get('/{id}', 'show')->name('show');
        });

        
        Route::controller(StorageController::class)->prefix('inventory')->name('storage.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });


        Route::controller(WholesaleStorageController::class)->prefix('wholesale-storage')->name('wholesale_storage.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::post('/transfer', 'transferToPharmacy')->name('transfer');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        
        Route::controller(WholesaleController::class)->prefix('wholesale')->name('wholesale.')->group(function () {
            Route::get('/', 'index')->name('index');           
            Route::get('/create', 'create')->name('create');   
            Route::post('/', 'store')->name('store');          
            Route::get('/{id}', 'show')->name('show');         
            Route::get('/{id}/edit', 'edit')->name('edit');    
            Route::put('/{id}', 'update')->name('update');     
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        
        Route::controller(EmployeeController::class)->prefix('employees')->name('employees.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
        });

        
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::controller(SaleController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/close-shift', 'closeShift')->name('close');
                Route::get('/report', 'showReport')->name('report');
                Route::delete('/delete/{id}', 'destroy')->name('destroy');
                Route::post('/cart/add', 'addToCart')->name('cart.add');
                Route::delete('/cart/{id}', 'removeFromCart')->name('cart.remove');
                Route::post('/checkout', 'checkout')->name('cart.checkout');
            });

            Route::controller(CustomerController::class)->prefix('customers')->name('customers.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/view/{transaction_id}', 'show')->name('show');
            });
        });
    });
});


Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ru', 'tm'])) { session()->put('locale', $locale); }
    return redirect()->back();
})->name('lang.switch');

Route::get('/wholesale/autocomplete', [WholesaleController::class, 'autocomplete'])->name('wholesale.autocomplete');
Route::get('/wholesale/export', [WholesaleController::class, 'exportExcel'])->name('wholesale.export');

Route::fallback(function () {
    return auth()->check() ? redirect()->route('welcome') : redirect()->route('login');
}); 
