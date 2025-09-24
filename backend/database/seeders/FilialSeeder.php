<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('filials')->insert([
            [
                'id' => 1,
                'ciudad' => 'Curico',
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
