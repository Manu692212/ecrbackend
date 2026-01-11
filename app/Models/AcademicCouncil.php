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
        'designation',
        'qualification',
    ];

    public $timestamps = true;
}
