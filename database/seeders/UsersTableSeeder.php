<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'roleId' => 1,
            'name' => 'Deelko',
            'email' => 'info@deelko.com',
            'password' => bcrypt('Deelko@'),
            'phone' => '01821100600',
            'active' => 1,
            'user_image' => ''
        ]);
    }
}
