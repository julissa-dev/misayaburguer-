<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    protected $fillable = [
        'usuario_id', 'total', 'estado', 'expira_en',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
