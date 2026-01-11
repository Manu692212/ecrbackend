<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class AcademicCouncil extends Model
{
    use HasFactory;

    protected $table = 'academic_councils';

    protected $fillable = [
        'name',
        'position',
        'email',
        'phone',
        'designation',
        'bio',
        'qualifications',
        'image',
        'image_size',
        'image_width',
        'image_height',
        'department',
        'order',
        'is_active',
    ];

    public $timestamps = true;
}
