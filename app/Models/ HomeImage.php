<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeImage extends Model
{
    protected $fillable = [
        'image_path',
        'title',
        'description',
        'order',
    ];
}
