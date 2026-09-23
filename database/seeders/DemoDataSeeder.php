<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 إنشاء بيانات تجريبية...');

        // إنشاء مستخدم إداري
        $admin = User::firstOrCreate(
            ['email' => 'hodifaabdhalmoaz@gmail.com'],
            [
                'name' => 'Hudhaifa Al-Hudhaifi',
                'password' => Hash::make('password123'),
                'utype' => 'ADM',
                'mobile' => '967777548421',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ تم إنشاء/تحديث المستخدم الإداري');

        // إنشاء مستخدم عادي للاختبار
        $user = User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'مستخدم تجريبي',
                'password' => Hash::make('password123'),
                'utype' => 'USR',
                'mobile' => '967123456789',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✅ تم إنشاء/تحديث مستخدم تجريبي');

        // إنشاء الفئات
        $categories = [
            ['name' => 'ألعاب الأطفال', 'slug' => 'toys', 'image' => 'toys.jpg'],
            ['name' => 'ملابس الأطفال', 'slug' => 'clothes', 'image' => 'clothes.jpg'],
            ['name' => 'كتب الأطفال', 'slug' => 'books', 'image' => 'books.jpg'],
            ['name' => 'أدوات التعلم', 'slug' => 'educational', 'image' => 'educational.jpg'],
            ['name' => 'العناية بالطفل', 'slug' => 'baby-care', 'image' => 'baby-care.jpg'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(['slug' => $categoryData['slug']], $categoryData);
        }
        $this->command->info('✅ تم إنشاء/تحديث ' . count($categories) . ' فئات');

        // إنشاء العلامات التجارية
        $brands = [
            ['name' => 'Fisher Price', 'slug' => 'fisher-price', 'image' => 'fisher-price.jpg'],
            ['name' => 'LEGO', 'slug' => 'lego', 'image' => 'lego.jpg'],
            ['name' => 'Mattel', 'slug' => 'mattel', 'image' => 'mattel.jpg'],
            ['name' => 'Hasbro', 'slug' => 'hasbro', 'image' => 'hasbro.jpg'],
            ['name' => 'Disney', 'slug' => 'disney', 'image' => 'disney.jpg'],
        ];

        foreach ($brands as $brandData) {
            Brand::firstOrCreate(['slug' => $brandData['slug']], $brandData);
        }
        $this->command->info('✅ تم إنشاء/تحديث ' . count($brands) . ' علامة تجارية');

        // إنشاء الألوان
        $colors = [
            ['name' => 'أحمر', 'code' => 'red'],
            ['name' => 'أزرق', 'code' => 'blue'],
            ['name' => 'أخضر', 'code' => 'green'],
            ['name' => 'أصفر', 'code' => 'yellow'],
            ['name' => 'وردي', 'code' => 'pink'],
        ];

        foreach ($colors as $colorData) {
            Color::firstOrCreate(['code' => $colorData['code']], $colorData);
        }
        $this->command->info('✅ تم إنشاء/تحديث ' . count($colors) . ' لون');

        // إنشاء الأحجام
        $sizes = [
            ['name' => 'صغير', 'code' => 'S'],
            ['name' => 'متوسط', 'code' => 'M'],
            ['name' => 'كبير', 'code' => 'L'],
            ['name' => 'كبير جداً', 'code' => 'XL'],
        ];

        foreach ($sizes as $sizeData) {
            Size::firstOrCreate(['code' => $sizeData['code']], $sizeData);
        }
        $this->command->info('✅ تم إنشاء/تحديث ' . count($sizes) . ' حجم');

        // إنشاء المنتجات
        $this->createProducts();

        $this->command->info('🎉 تم إنشاء جميع البيانات التجريبية بنجاح!');
        $this->command->info('📧 بيانات الدخول للإدارة:');
        $this->command->info('   البريد: hodifaabdhalmoaz@gmail.com');
        $this->command->info('   كلمة المرور: password123');
    }

    private function createProducts()
    {
        $products = [
            [
                'name' => 'مكعبات ليجو كلاسيك',
                'slug' => 'lego-classic-blocks',
                'short_description' => 'مجموعة مكعبات ليجو ملونة للإبداع',
                'description' => 'مجموعة رائعة من مكعبات ليجو الملونة التي تساعد الأطفال على تطوير مهاراتهم الإبداعية والحركية. مناسبة للأعمار من 4 سنوات فما فوق.',
                'SKU' => 'TOY001',
                'regular_price' => 150.00,
                'sale_price' => 120.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 50,
                'image' => 'lego-blocks.jpg',
                'category_id' => 1,
                'brand_id' => 2,
                'views' => 245,
            ],
            [
                'name' => 'قميص أطفال قطني',
                'slug' => 'cotton-kids-shirt',
                'short_description' => 'قميص قطني ناعم ومريح للأطفال',
                'description' => 'قميص مصنوع من القطن الطبيعي 100% بألوان زاهية ومقاسات متنوعة. مناسب للاستخدام اليومي ومقاوم للغسيل.',
                'SKU' => 'CLO001',
                'regular_price' => 45.00,
                'sale_price' => 45.00,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 100,
                'image' => 'kids-shirt.jpg',
                'category_id' => 2,
                'brand_id' => 1,
                'views' => 89,
            ],
            [
                'name' => 'كتاب قصص الأطفال المصور',
                'slug' => 'illustrated-children-stories',
                'short_description' => 'مجموعة قصص مصورة تعليمية للأطفال',
                'description' => 'كتاب يحتوي على 20 قصة مصورة باللغة العربية تهدف إلى تعليم الأطفال القيم والأخلاق الحميدة بطريقة ممتعة وتفاعلية.',
                'SKU' => 'BOO001',
                'regular_price' => 35.00,
                'sale_price' => 28.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 75,
                'image' => 'story-book.jpg',
                'category_id' => 3,
                'brand_id' => 3,
                'views' => 156,
            ],
            [
                'name' => 'لوحة تعليمية تفاعلية',
                'slug' => 'interactive-learning-board',
                'short_description' => 'لوحة إلكترونية تعليمية للحروف والأرقام',
                'description' => 'لوحة تعليمية تفاعلية تساعد الأطفال على تعلم الحروف والأرقام والألوان بطريقة ممتعة مع الأصوات والموسيقى.',
                'SKU' => 'EDU001',
                'regular_price' => 200.00,
                'sale_price' => 180.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 30,
                'image' => 'learning-board.jpg',
                'category_id' => 4,
                'brand_id' => 1,
                'views' => 312,
            ],
            [
                'name' => 'شامبو أطفال لطيف',
                'slug' => 'gentle-baby-shampoo',
                'short_description' => 'شامبو لطيف ومناسب لبشرة الأطفال الحساسة',
                'description' => 'شامبو مصمم خصيصاً للأطفال بتركيبة لطيفة خالية من المواد الكيميائية الضارة. يحافظ على نعومة شعر الطفل ولا يسبب تهيج العينين.',
                'SKU' => 'CAR001',
                'regular_price' => 25.00,
                'sale_price' => 25.00,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 80,
                'image' => 'baby-shampoo.jpg',
                'category_id' => 5,
                'brand_id' => 4,
                'views' => 67,
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(['SKU' => $productData['SKU']], $productData);
        }

        $this->command->info('✅ تم إنشاء/تحديث ' . count($products) . ' منتج');
    }
}
