<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'client_image',
        'company',
        'role',
        'text',
        'rating',
        'project_type',
        'featured',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'featured' => 'boolean',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
    ];
}
