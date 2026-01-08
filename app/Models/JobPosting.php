<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'department',
        'description',
        'requirements',
        'location',
        'employment_type',
        'salary_min',
        'salary_max',
        'deadline',
        'is_active',
        'order'
    ];

    protected $casts = [
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'deadline' => 'date',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}
