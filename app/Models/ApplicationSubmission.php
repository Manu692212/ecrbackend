<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type',
        'full_name',
        'email',
        'phone',
        'title',
        'status',
        'payload',
        'admin_notes',
        'admin_viewed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'admin_viewed_at' => 'datetime',
    ];
}
