<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RajaOngkirController;
use App\Http\Controllers\RajaOngkirControllerV2;



Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('beranda');
});
Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])->name('backend.beranda')->middleware('auth');
Route::get('backend/login', [LoginController::class, 'loginBackend'])->name('backend.login');
Route::post('backend/login', [LoginController::class, 'authenticateBackend'])->name('backend.login');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');

// Route::resource('backend/user', UserController::class)->middleware('auth');
Route::resource('backend/user', UserController::class, ['as' => 'backend'])->middleware('auth');
// Route untuk laporan user
Route::get('backend/laporan/formuser', [UserController::class, 'formUser'])->name('backend.laporan.formuser')->middleware('auth');
Route::post('backend/laporan/cetakuser', [UserController::class, 'cetakUser'])->name('backend.laporan.cetakuser')->middleware('auth');
Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend'])->middleware('auth');
Route::resource('backend/produk', ProdukController::class, ['as' => 'backend'])->middleware('auth');
Route::resource('backend/customer', CustomerController::class, ['as' => 'backend'])->middleware('auth');
// Route untuk menambahkan foto
Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])->name('backend.foto_produk.store')->middleware('auth');
// Route untuk menghapus foto
Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])->name('backend.foto_produk.destroy')->middleware('auth');
// Route untuk laporan produk
Route::get('backend/laporan/formproduk', [ProdukController::class, 'formProduk'])->name('backend.laporan.formproduk')->middleware('auth');
Route::post('backend/laporan/cetakproduk', [ProdukController::class, 'cetakProduk'])->name('backend.laporan.cetakproduk')->middleware('auth');

// Frontend
Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('/produk/detail/{id}', [ProdukController::class, 'detail'])->name('produk.detail');
Route::get('/produk/kategori/{id}', [ProdukController::class, 'produkKategori'])->name('produk.kategori');
Route::get('/produk/all', [ProdukController::class, 'produkAll'])->name('produk.all');

//API Google
Route::get('/auth/redirect', [CustomerController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/google/callback', [CustomerController::class, 'callback'])->name('auth.callback');
// Logout
Route::post('/logout', [CustomerController::class, 'logout'])->name('customer.logout');
// Group route untuk customer
Route::middleware('is.customer')->group(function () {
    // Route untuk menampilkan halaman akun customer
    Route::get('/customer/akun/{id}', [CustomerController::class, 'akun'])->name('customer.akun');
    // Route untuk mengupdate data akun customer
    Route::put('/customer/updateakun/{id}', [CustomerController::class, 'updateAkun'])->name('customer.updateakun');
    // Route untuk menambahkan produk ke keranjang
    Route::post('add-to-cart/{id}', [OrderController::class, 'addToCart'])->name('order.addToCart');
    Route::get('cart', [OrderController::class, 'viewCart'])->name('order.cart');
    Route::post('cart/update/{id}', [OrderController::class, 'updateCart'])->name('order.updateCart');
    Route::post('remove/{id}', [OrderController::class, 'removeFromCart'])->name('order.remove');
    // Ongkir
    Route::post('select-shipping', [OrderController::class, 'selectShipping'])->name('order.selectShipping');
    Route::post('update-ongkir', [OrderController::class, 'updateOngkir'])->name('order.update-ongkir');
    Route::get('select-payment', [OrderController::class, 'selectPayment'])->name('order.selectpayment');
    Route::get('provinces', [OrderController::class, 'getProvinces']);
    Route::get('cities', [OrderController::class, 'getCities']);
    Route::post('cost', [OrderController::class, 'getCost']);
    Route::post('updateongkir', [OrderController::class, 'updateongkir'])->name('order.update-ongkir');
    //midtrans
    Route::get('select-payment', [OrderController::class, 'selectPayment'])->name('order.selectpayment');
    Route::post('/midtrans-callback', [OrderController::class, 'callback']);
    Route::get('/order/complete', [OrderController::class, 'complete'])->name('order.complete');
    // Route history
    Route::get('history', [OrderController::class, 'orderHistory'])->name('order.history');
    Route::get('order/invoice/{id}', [OrderController::class, 'invoiceFrontend'])->name('order.invoice');
});

// Route untuk menampilkan halaman detail dan ubah customer
Route::get('/backend/detailCustomer/{id}', [CustomerController::class, 'show'])->name('backend.customer.detail');
Route::get('/backend/editCustomer/{id}', [CustomerController::class, 'edit'])->name('backend.customer.edit');
Route::put('/backend/updateCustomer/{id}', [CustomerController::class, 'update'])->name('backend.customer.update');

// raja ongkir
Route::get('/list-ongkir', function () {
    $response = Http::withHeaders([
        'key' => '794a5d197b9cb469ae958ed043ccf921'
    ])->get('https://api.rajaongkir.com/starter/province'); //ganti 'province' atau 'city'
    dd($response->json());
});

Route::get('/cek-ongkir', function () {
    return view('ongkir');
});
Route::get('/provinces', [RajaOngkirController::class, 'getProvinces']);
Route::get('/cities', [RajaOngkirController::class, 'getCities']);
Route::post('/cost', [RajaOngkirController::class, 'getCost']);


// cek_raja_ongkir_v2
Route::get('/cek_raja_ongkir_v2', function () {
    return view('ongkir_v2');
});
Route::get('/ongkir/get-destination', [RajaOngkirControllerV2::class, 'getDestination']);
Route::post('/ongkir/calculate', [RajaOngkirControllerV2::class, 'calculateOngkir']);
