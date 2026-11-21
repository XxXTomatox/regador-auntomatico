<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ArduinoController;
use App\Models\Configuracion; // <-- AÑADIR ESTA LÍNEA
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Modificamos esta ruta para que use tu controlador
Route::get('/dashboard', [ArduinoController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Aquí está el grupo de rutas protegidas por el login
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- RUTA PARA EL BOTÓN DE LA BOMBA ---
    Route::post('/bomba/toggle', function () {
        $config = Configuracion::first();
        
        if (!$config) {
            return response()->json(['status' => 'error', 'mensaje' => 'No hay configuración en la BD'], 500);
        }

        $config->bomba_manual = !$config->bomba_manual; 
        $config->save();
        
        return response()->json(['status' => 'success', 'estado' => $config->bomba_manual]);
    })->name('bomba.toggle');
});

require __DIR__.'/auth.php';