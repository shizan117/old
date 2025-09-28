<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('Configs')->insert([
                [
                    'config_title' => 'companyName',
                    'value' => 'One Uproar'
                ],
                [
                    'config_title' => 'logo',
                    'value' => 'logo.png'
                ],
                [
                    'config_title' => 'address',
                    'value' => '706/5 Ishibpur, Nakla <br> Sherpur - 2150'
                ],
                [
                    'config_title' => 'phone',
                    'value' => '01712377506'
                ],
                [
                    'config_title' => 'currencyCode',
                    'value' => 'Tk.'
                ],
                [
                    'config_title' => 'name_operator',
                    'value' => ''
                ],
                [
                    'config_title' => 'type_of_client',
                    'value' => ''
                ],
                [
                    'config_title' => 'vatRate',
                    'value' => '0'
                ],
                [
                    'config_title' => 'exp_date',
                    'value' => '7'
                ],
                [
                    'config_title' => 'bkash_username',
                    'value' => ''
                ],
                [
                    'config_title' => 'bkash_password',
                    'value' => ''
                ],
                [
                    'config_title' => 'bkash_app_key',
                    'value' => ''
                ],
                [
                    'config_title' => 'bkash_app_secret',
                    'value' => ''
                ],
                [
                    'config_title' => 'bkash_checkout_script_url',
                    'value' => ''
                ],
                [
                    'config_title' => 'bkash_pr_root_url',
                    'value' => ''
                ],
                [
                    'config_title' => 'bkash_charge',
                    'value' => ''
                ],
            ]

        );
    }
}
