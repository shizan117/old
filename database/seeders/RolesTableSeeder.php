<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
                [
                    'roleName' => 'Super Admin',
                    'roleType' => 1
                ],
                [
                    'roleName' => 'Admin',
                    'roleType' => 2
                ],
                [
                    'roleName' => 'Branch User',
                    'roleType' => 3
                ],
                [
                    'roleName' => 'Reseller',
                    'roleType' => 4
                ],
                [
                    'roleName' => 'Accountant',
                    'roleType' => 5
                ]
            ]

        );
    }
}
