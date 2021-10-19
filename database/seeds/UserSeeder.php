<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'first_name' => "Иванов",
            'last_name' => "Иван",
            'middle_name' => "Иванович",
            'position' => "Должность",
            'company' => "Smart Control Corp.",
            'email' => "ivanov@tester.ru",
            'role_id' => Role::first()->id,
            'created_at' => \Carbon\Carbon::now(),
            'password' => Hash::make('tester'),
        ]);
    }
}
