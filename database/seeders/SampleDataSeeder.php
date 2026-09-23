<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء العلامات التجارية
        $brands = [
            ['name' => 'كارترز', 'slug' => 'carters', 'image' => 'brands/carters.png'],
            ['name' => 'جيربر', 'slug' => 'gerber', 'image' => 'brands/gerber.png'],
            ['name' => 'نايكي كيدز', 'slug' => 'nike-kids', 'image' => 'brands/nike-kids.png'],
            ['name' => 'أديداس كيدز', 'slug' => 'adidas-kids', 'image' => 'brands/adidas-kids.png'],
            ['name' => 'زارا كيدز', 'slug' => 'zara-kids', 'image' => 'brands/zara-kids.png'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // إنشاء الفئات
        $categories = [
            [
                'name' => 'ملابس الأولاد',
                'slug' => 'boys-clothing',
                'image' => 'categories/boys-clothing.jpg',
                'featured' => 1,
                'status' => 1
            ],
            [
                'name' => 'ملابس البنات',
                'slug' => 'girls-clothing',
                'image' => 'categories/girls-clothing.jpg',
                'featured' => 1,
                'status' => 1
            ],
            [
                'name' => 'أحذية الأطفال',
                'slug' => 'kids-shoes',
                'image' => 'categories/kids-shoes.jpg',
                'featured' => 1,
                'status' => 1
            ],
            [
                'name' => 'ألعاب الأطفال',
                'slug' => 'kids-toys',
                'image' => 'categories/kids-toys.jpg',
                'featured' => 0,
                'status' => 1
            ],
            [
                'name' => 'مستلزمات الرضع',
                'slug' => 'baby-essentials',
                'image' => 'categories/baby-essentials.jpg',
                'featured' => 1,
                'status' => 1
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // إنشاء المنتجات
        $products = [
            [
                'name' => 'طقم ملابس أولاد قطني',
                'slug' => 'boys-cotton-clothing-set',
                'short_description' => 'طقم ملابس مريح للأولاد من القطن الطبيعي',
                'description' => 'طقم ملابس عالي الجودة للأولاد مصنوع من القطن الطبيعي 100%. يتضمن قميص وبنطلون مريحين ومناسبين للاستخدام اليومي.',
                'regular_price' => 150.00,
                'sale_price' => 120.00,
                'SKU' => 'BOY-SET-001',
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 50,
                'image' => 'products/boys-set-1.jpg',
                'category_id' => 1,
                'brand_id' => 1,
            ],
            [
                'name' => 'فستان بنات أنيق',
                'slug' => 'elegant-girls-dress',
                'short_description' => 'فستان أنيق للبنات مناسب للمناسبات',
                'description' => 'فستان جميل وأنيق للبنات مصنوع من أقمشة عالية الجودة. مناسب للمناسبات الخاصة والحفلات.',
                'regular_price' => 200.00,
                'sale_price' => 180.00,
                'SKU' => 'GIRL-DRESS-001',
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 30,
                'image' => 'products/girls-dress-1.jpg',
                'category_id' => 2,
                'brand_id' => 2,
            ],
            [
                'name' => 'حذاء رياضي للأطفال',
                'slug' => 'kids-sports-shoes',
                'short_description' => 'حذاء رياضي مريح للأطفال',
                'description' => 'حذاء رياضي عالي الجودة للأطفال مع نعل مريح ومقاوم للانزلاق. مناسب للأنشطة الرياضية واللعب.',
                'regular_price' => 250.00,
                'sale_price' => null,
                'SKU' => 'SHOES-001',
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 25,
                'image' => 'products/kids-shoes-1.jpg',
                'category_id' => 3,
                'brand_id' => 3,
            ],
            [
                'name' => 'لعبة تعليمية للأطفال',
                'slug' => 'educational-toy',
                'short_description' => 'لعبة تعليمية تنمي مهارات الطفل',
                'description' => 'لعبة تعليمية ممتعة تساعد على تنمية مهارات الطفل الذهنية والحركية. آمنة ومصنوعة من مواد غير سامة.',
                'regular_price' => 80.00,
                'sale_price' => 65.00,
                'SKU' => 'TOY-001',
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 40,
                'image' => 'products/educational-toy-1.jpg',
                'category_id' => 4,
                'brand_id' => 4,
            ],
            [
                'name' => 'مجموعة مستلزمات الرضع',
                'slug' => 'baby-essentials-set',
                'short_description' => 'مجموعة شاملة من مستلزمات الرضع',
                'description' => 'مجموعة شاملة تحتوي على جميع المستلزمات الأساسية للرضع. تشمل ملابس، بطانيات، وإكسسوارات ضرورية.',
                'regular_price' => 300.00,
                'sale_price' => 250.00,
                'SKU' => 'BABY-SET-001',
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 20,
                'image' => 'products/baby-essentials-1.jpg',
                'category_id' => 5,
                'brand_id' => 5,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
