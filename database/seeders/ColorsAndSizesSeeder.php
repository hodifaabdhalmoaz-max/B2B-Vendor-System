<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Color;
use App\Models\Size;

class ColorsAndSizesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إضافة الألوان
        $colors = [
            ['name' => 'أحمر', 'code' => 'red', 'hex_code' => '#FF0000'],
            ['name' => 'أزرق', 'code' => 'blue', 'hex_code' => '#0000FF'],
            ['name' => 'أخضر', 'code' => 'green', 'hex_code' => '#00FF00'],
            ['name' => 'أصفر', 'code' => 'yellow', 'hex_code' => '#FFFF00'],
            ['name' => 'أسود', 'code' => 'black', 'hex_code' => '#000000'],
            ['name' => 'أبيض', 'code' => 'white', 'hex_code' => '#FFFFFF'],
            ['name' => 'وردي', 'code' => 'pink', 'hex_code' => '#FFC0CB'],
            ['name' => 'بنفسجي', 'code' => 'purple', 'hex_code' => '#800080'],
            ['name' => 'برتقالي', 'code' => 'orange', 'hex_code' => '#FFA500'],
            ['name' => 'بني', 'code' => 'brown', 'hex_code' => '#A52A2A'],
        ];

        foreach ($colors as $color) {
            Color::updateOrCreate(['code' => $color['code']], $color);
        }

        // إضافة الأحجام
        $sizes = [
            ['name' => 'صغير جداً', 'code' => 'XS', 'order' => 1],
            ['name' => 'صغير', 'code' => 'S', 'order' => 2],
            ['name' => 'متوسط', 'code' => 'M', 'order' => 3],
            ['name' => 'كبير', 'code' => 'L', 'order' => 4],
            ['name' => 'كبير جداً', 'code' => 'XL', 'order' => 5],
            ['name' => 'كبير جداً جداً', 'code' => 'XXL', 'order' => 6],
            ['name' => '6 أشهر', 'code' => '6M', 'order' => 7],
            ['name' => '12 شهر', 'code' => '12M', 'order' => 8],
            ['name' => '18 شهر', 'code' => '18M', 'order' => 9],
            ['name' => '24 شهر', 'code' => '24M', 'order' => 10],
            ['name' => '3 سنوات', 'code' => '3Y', 'order' => 11],
            ['name' => '4 سنوات', 'code' => '4Y', 'order' => 12],
            ['name' => '5 سنوات', 'code' => '5Y', 'order' => 13],
            ['name' => '6 سنوات', 'code' => '6Y', 'order' => 14],
        ];

        foreach ($sizes as $size) {
            Size::updateOrCreate(['code' => $size['code']], $size);
        }

        $this->command->info('تم إضافة الألوان والأحجام بنجاح!');
    }
}
