<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KegiatanController;
use App\Http\Controllers\Api\PassportAuthController;
use App\Http\Controllers\Api\PublicController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);
Route::get('users', [PublicController::class, 'index']);
Route::get('users/{id}', [PublicController::class, 'indexById']);
Route::post('users/create', [PublicController::class, 'create']);
Route::put('users/update/{id}', [PublicController::class, 'update']);
Route::delete('users/delete/{id}', [PublicController::class, 'destroy']);

Route::get('GetKey', [PublicController::class, 'GetKey']);  // Get Key

Route::middleware('check.header')->group( function () {
    Route::get('get-user', [PassportAuthController::class, 'userInfo']);
    Route::get('kegiatan', [KegiatanController::class, 'index']); // Datatables
    Route::get('statistik', [KegiatanController::class, 'statistik']); // Statistik pertanggal
    Route::get('uklupl_sppl', [KegiatanController::class, 'uklupl_sppl']); // Jumlah UKL-UPL MR dan SPPL
    Route::get('uklupl_pusat', [KegiatanController::class, 'uklupl_pusat']); // Jumlah data UKL-UPL MR per kewenangan di Admin Pusat
    Route::get('sppl_pusat', [KegiatanController::class, 'sppl_pusat']); // Jumlah data SPPL per kewenangan di Admin Pusat
    Route::get('jml_prov', [KegiatanController::class, 'jml_prov']); // Jumlah UKL-UPL MR per provinsi di Admin Pusat
    Route::get('jml_kegiatan', [KegiatanController::class, 'jml_kegiatan']);
    Route::get('total', [KegiatanController::class, 'total']);
    Route::get('filteredTotal', [KegiatanController::class, 'filteredTotal']);
    Route::get('cluster', [KegiatanController::class, 'cluster']);
    Route::get('uklupl_sppl_tot', [KegiatanController::class, 'uklupl_sppl_tot']);
});