<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArduinoController;
use App\Models\Configuracion;

Route::post('/arduino/data', [ArduinoController::class, 'store']);
Route::get('/arduino/latest', [ArduinoController::class, 'latest']);
Route::get('/arduino/bomba-estado', function () {
    $config = Configuracion::first();
    // (bool) fuerza a que devuelva true o false real
    return response()->json(['bomba_manual' => (bool) ($config ? $config->bomba_manual : false)]);
});