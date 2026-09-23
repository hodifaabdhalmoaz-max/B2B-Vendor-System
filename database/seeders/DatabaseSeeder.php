<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // تشغيل البيانات الأولية للمستخدمين الإداريين
        $this->call([
            AdminUserSeeder::class,
            SettingsSeeder::class,
            SampleDataSeeder::class,
        ]);
    }
}
