<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء المستخدم الإداري الرئيسي
        User::create([
            'name' => 'حذيفة الحذيفي',
            'email' => 'admin@kids-store.com',
            'password' => Hash::make('admin123'),
            'mobile' => '966501234567',
            'utype' => 'ADM',
            'email_verified_at' => now(),
        ]);

        // إنشاء مستخدم عادي للاختبار
        User::create([
            'name' => 'مستخدم تجريبي',
            'email' => 'user@kids-store.com',
            'password' => Hash::make('user123'),
            'mobile' => '966507654321',
            'utype' => 'USR',
            'email_verified_at' => now(),
        ]);
    }
}
