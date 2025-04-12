<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->insert([
            'topup_fee' => 10, // ตัวอย่างค่าธรรมเนียม
            'topup_phone' => '0123456789', // ตัวอย่างเบอร์โทร
            'robux_min' => 10, // จำนวนเงินขั้นต่ำในการเติม
            'robux_rate' => 0.5, // ตัวอย่างเรทโรบัค
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
