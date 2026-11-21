<?php

namespace App\Http\Controllers;

use App\Models\SensorData;
use Illuminate\Http\Request;

class ArduinoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'peso' => 'nullable|numeric',
            'alerta_termica' => 'boolean',
            'bomba_encendida' => 'boolean',
            'alerta_peso' => 'boolean',
        ]);

        SensorData::create($validated);
        return response()->json(['status' => 'success'], 201);
    }

    public function latest()
    {
        return response()->json(SensorData::latest()->first());
    }

    public function index()
    {
        return view('dashboard'); // Usaremos el dashboard de Breeze
    }
}