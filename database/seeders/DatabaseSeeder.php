<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\WalletType;
use App\Models\Category;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Role::insert([
           ['name' => 'admin'],
           ['name' => 'guest'],
           ['name' => 'user'],
           ['name' => 'premium_user'],
        ]);

        $roles = Role::pluck('id', 'name');

        $admin = User::firstOrCreate(
            ['email' => 'dinhvanluan2k3@gmail.com'],
            [
               'name' => 'Admin',
               'password' => Hash::make('admin12356'),
               'provider_auth' => 'email',
            ]
        );

        if (!$admin->roles()->where('role_id', $roles['admin'])->exists()) {
            $admin->roles()->attach($roles['admin']);
        }

        User::factory(20)->create()->each(function ($user) use ($roles) {
            $user->roles()->attach($roles['user']);

            if (rand(1, 100) <= 20) {
                $user->roles()->attach($roles['premium_user']);
            }
        });

        WalletType::factory()->count(4)->create();

        $walletTypeIds = WalletType::pluck('id')->toArray();

        Category::factory()
            ->count(10)
            ->create()
            ->each(function ($category) use ($walletTypeIds) {
                $category->walletTypes()->attach(
                    collect($walletTypeIds)->random(rand(1, 3))->all()
                );
            });

        $this->call([
            CurrencySeeder::class,
            UserSettingSeeder::class,
        ]);
    }
}
