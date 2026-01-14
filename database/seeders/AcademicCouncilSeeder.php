<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicCouncilSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('academic_councils')->insert([
            [
                'name' => 'Dr Ramesh Kumar',
                'designation' => 'Chairman – Academic Council',
                'qualification' => 'PhD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dr Anitha Rao',
                'designation' => 'Council Member',
                'qualification' => 'PhD',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
