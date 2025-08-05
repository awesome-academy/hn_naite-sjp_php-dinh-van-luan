<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserSetting;

class UserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        for ($userId = 2; $userId <= 21; $userId++) {
            UserSetting::updateOrCreate(
                ['user_id' => $userId],
                [
                    'currency_id' => rand(1, 3),
                    'language_default' => ['en', 'vi'][rand(0, 1)],
                ]
            );
        }
    }
}
