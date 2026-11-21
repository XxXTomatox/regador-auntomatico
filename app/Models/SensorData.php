<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    protected $fillable = ['temperatura', 'humedad', 'peso', 'alerta_termica', 'bomba_encendida', 'alerta_peso'];
    //
}
