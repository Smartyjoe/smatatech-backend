<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'company',
        'phone',
        'project_type',
        'budget',
        'services',
        'message',
        'read',
    ];

    protected $casts = [
        'services' => 'array',
        'read' => 'boolean',
    ];
}
