<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'website',
        'status',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
    ];
}
