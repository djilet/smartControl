<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => "Администратор",
            'created_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('roles')->insert([
            'name' => "Руководитель",
            'created_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('roles')->insert([
            'name' => "Инженер ПТО",
            'created_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('roles')->insert([
            'name' => "Инженер строительного контроля",
            'created_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('roles')->insert([
            'name' => "Подрядная организация",
            'created_at' => \Carbon\Carbon::now(),
        ]);
    }
}
