<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'DIV_CD_MONITORIA']);
        Permission::firstOrCreate(['name' => 'DIV_MONITORIA_DIS']);
        Permission::firstOrCreate(['name' => 'HEADER_CD']);
        Permission::firstOrCreate(['name' => 'RPT_CD_DISCIPLINA']);
        Permission::firstOrCreate(['name' => 'RPT_CD_DOCENTE']);
        Permission::firstOrCreate(['name' => 'HEADER_MONITORIA']);
        Permission::firstOrCreate(['name' => 'RPT_MONITORIA']);
        Permission::firstOrCreate(['name' => 'HEADER_DIS']);
        Permission::firstOrCreate(['name' => 'RPT_DIS_ING']);
        Permission::firstOrCreate(['name' => 'RPT_DIS_EST']);
    }
}
