<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KegiatanController;
use App\Http\Controllers\Api\PassportAuthController;

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

Route::middleware('auth:sanctum')->group( function () {
    Route::get('get-user', [PassportAuthController::class, 'userInfo']);
    Route::get('kegiatan', [KegiatanController::class, 'index']); // Datatables
    Route::get('statistik', [KegiatanController::class, 'statistik']); // Statistik pertanggal
    Route::get('uklupl_sppl', [KegiatanController::class, 'uklupl_sppl']); // Jumlah UKL-UPL MR dan SPPL
    Route::get('uklupl_pusat', [KegiatanController::class, 'uklupl_pusat']); // Jumlah data UKL-UPL MR per kewenangan di Admin Pusat
    Route::get('sppl_pusat', [KegiatanController::class, 'sppl_pusat']); // Jumlah data SPPL per kewenangan di Admin Pusat
    Route::get('jml_prov', [KegiatanController::class, 'jml_prov']); // Jumlah UKL-UPL MR per provinsi di Admin Pusat
    Route::get('jml_kegiatan', [KegiatanController::class, 'jml_kegiatan']);
    Route::get('total', [KegiatanController::class, 'total']);
});