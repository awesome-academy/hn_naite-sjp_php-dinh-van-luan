<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServicePackage;

class ServicePackageSeeder extends Seeder
{
    public function run()
    {
        ServicePackage::updateOrCreate(
            ['title' => 'Premium'],
            [
                'description' => 'Trở thành Khách hàng Premium, có thể tạo ngân sách định kỳ, tạo giao dịch định kỳ không giới hạn.',
                'price' => 99000
            ]
        );
    }
}
