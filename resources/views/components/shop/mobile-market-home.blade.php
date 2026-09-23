@props([
    'categories',
    'featuredProducts',
    'latestProducts',
    'offerProducts',
    'marketProducts' => collect(),
    'activeCategory' => 'all',
    'promotions' => [],
])

@php
    $isKids = $activeCategory === 'kids';
    $isThemedMarket = in_array($activeCategory, ['gifts', 'toys', 'mother', 'care', 'accessories', 'shoes'], true);

    $resolveImage = function ($path, $folder) {
        if (empty($path)) {
            return asset('assets/images/home/demo3/category_1.png');
        }

        return \Illuminate\Support\Str::startsWith($path, $folder.'/')
            ? asset('uploads/'.$path)
            : asset('uploads/'.$folder.'/'.$path);
    };

    $resolveStaticImage = function ($path) {
        if (\Illuminate\Support\Str::startsWith($path, 'products/')) {
            return asset('assets/images/'.$path);
        }

        return file_exists(public_path('assets/images/home/demo3/'.$path))
            ? asset('assets/images/home/demo3/'.$path)
            : asset('assets/images/'.$path);
    };

    $marketProducts = collect($marketProducts);

    $assignedProductsForSection = function (string $section, int $limit = 16) use ($marketProducts) {
        return $marketProducts
            ->filter(fn ($product) => in_array($section, $product->storefront_sections ?? [], true))
            ->sortBy([
                ['storefront_order', 'asc'],
                ['created_at', 'desc'],
            ])
            ->values()
            ->take($limit);
    };

    $categoriesForContext = function (string $context, $fallback = null) use ($categories) {
        $assigned = collect($categories)
            ->filter(fn ($category) => in_array($context, $category->storefront_contexts ?? [], true))
            ->sortBy([
                ['storefront_order', 'asc'],
                ['name', 'asc'],
            ])
            ->values();

        if ($assigned->isNotEmpty()) {
            return $assigned;
        }

        return collect($fallback ?? []);
    };

    $fallbackCategories = collect([
        ['name' => 'ملابس الأطفال', 'image' => 'category_1.png', 'url' => route('shop.search', ['search' => 'ملابس أطفال'])],
        ['name' => 'هدايا', 'image' => 'category_2.png', 'url' => route('shop.search', ['search' => 'هدايا'])],
        ['name' => 'العاب وتعليم', 'image' => 'category_3.png', 'url' => route('shop.search', ['search' => 'العاب تعليمية'])],
        ['name' => 'مستلزمات الأمومة', 'image' => 'category_5.png', 'url' => route('shop.search', ['search' => 'مستلزمات الأمومة'])],
        ['name' => 'الصحة والعناية', 'image' => 'category_6.png', 'url' => route('shop.search', ['search' => 'العناية'])],
        ['name' => 'اكسسوارات', 'image' => 'category_7.png', 'url' => route('shop.search', ['search' => 'اكسسوارات'])],
        ['name' => 'أحذية', 'image' => 'category_9.jpg', 'url' => route('shop.search', ['search' => 'أحذية'])],
        ['name' => 'حديثي الولادة', 'image' => 'category_10.jpg', 'url' => route('shop.search', ['search' => 'حديثي الولادة'])],
    ]);

    $kidsAgeTabs = collect([
        ['id' => 'category', 'label' => 'الفئة', 'context' => 'kids.category', 'active' => true],
        ['id' => '0_12', 'label' => '0-12 شهر', 'context' => 'kids.0_12', 'active' => false],
        ['id' => '12_24', 'label' => '12-24 شهر', 'context' => 'kids.12_24', 'active' => false],
        ['id' => '3_6', 'label' => '3-6 سنوات', 'context' => 'kids.3_6', 'active' => false],
    ]);

    $kidsCategories = collect([
        ['name' => 'بنات رضع', 'age' => '0-3 سنوات', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'بنات رضع'],
        ['name' => 'بنات صغار', 'age' => '4-7 سنوات', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'ملابس بنات صغار'],
        ['name' => 'بنات 8-12', 'age' => '8-12 سنة', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'search' => 'ملابس بنات 8 12'],
        ['name' => 'بنات مراهقات', 'age' => '13-16 سنة', 'image' => 'category_1.png', 'search' => 'ملابس بنات مراهقات'],
        ['name' => 'أولاد رضع', 'age' => '0-3 سنوات', 'image' => 'Baby Boy Clothes & Outfits.jpg', 'search' => 'أولاد رضع'],
        ['name' => 'أولاد صغار', 'age' => '4-7 سنوات', 'image' => 'RIVER FINLEY & FRIENDS – River Finley & Friends.jpg', 'search' => 'ملابس أولاد صغار'],
        ['name' => 'أولاد 8-12', 'age' => '8-12 سنة', 'image' => 'kkkk.png', 'search' => 'ملابس أولاد 8 12'],
        ['name' => 'أولاد مراهقون', 'age' => '13-16 سنة', 'image' => 'category_2.png', 'search' => 'ملابس أولاد مراهقين'],
        ['name' => 'صيفي', 'age' => null, 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'search' => 'ملابس أطفال صيفية'],
        ['name' => 'مستلزمات الرضع', 'age' => null, 'image' => 'Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg', 'search' => 'مستلزمات الرضع'],
        ['name' => 'أحذية', 'age' => null, 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'search' => 'أحذية أطفال'],
        ['name' => 'شنط واكسسوارات', 'age' => null, 'image' => 'category_7.png', 'search' => 'اكسسوارات أطفال'],
    ]);

    $kidsCollections = collect([
        ['id' => 'summer', 'title' => 'إطلالات الصيف', 'section' => 'kids.summer', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'ملابس أطفال صيفية'],
        ['id' => 'brands', 'title' => 'ماركات مختارة', 'section' => 'kids.brands', 'image' => 'RIVER FINLEY & FRIENDS – River Finley & Friends.jpg', 'search' => 'ماركات أطفال'],
        ['id' => 'characters', 'title' => 'شخصيات محبوبة', 'section' => 'kids.characters', 'image' => 'kkkk.png', 'search' => 'شخصيات أطفال'],
        ['id' => 'cotton', 'title' => 'قطن ناعم', 'section' => 'kids.cotton', 'image' => 'Baby Boy Clothes & Outfits.jpg', 'search' => 'ملابس أطفال قطن'],
        ['id' => 'family', 'title' => 'أطقم العائلة', 'section' => 'kids.family', 'image' => 'category_5.png', 'search' => 'أطقم أطفال'],
    ]);

    $kidsCategoryFallbacks = collect([
        'kids.category' => $kidsCategories,
        'kids.0_12' => $kidsCategories->filter(fn ($category) => str_contains($category['age'] ?? '', '0-3'))->values(),
        'kids.12_24' => $kidsCategories->filter(fn ($category) => in_array($category['name'], ['بنات رضع', 'أولاد رضع', 'مستلزمات الرضع'], true))->values(),
        'kids.3_6' => $kidsCategories->filter(fn ($category) => str_contains($category['age'] ?? '', '4-7'))->values(),
    ]);

    $displayCategory = function ($category) use ($resolveImage, $resolveStaticImage) {
        if (is_array($category)) {
            return [
                'name' => $category['name'],
                'age' => $category['age'] ?? null,
                'url' => route('shop.search', ['search' => $category['search'] ?? $category['name']]),
                'image' => $resolveStaticImage($category['image']),
            ];
        }

        return [
            'name' => $category->name,
            'age' => null,
            'url' => route('shop.category', $category->slug),
            'image' => $resolveImage($category->image, 'categories'),
        ];
    };

    $themeSections = [
        'gifts' => [
            'category' => 'gifts.category',
            'for_you' => 'gifts.for_you',
            'collections' => ['gifts.safe', 'gifts.newborn', 'gifts.educational', 'gifts.wrapping', 'gifts.popular'],
            'spotlights' => ['gifts.today', 'gifts.new_arrivals'],
        ],
        'toys' => [
            'category' => 'toys.category',
            'for_you' => 'toys.for_you',
            'collections' => ['toys.safe', 'toys.brands', 'toys.baby_kids', 'toys.educational', 'toys.new_arrivals'],
            'spotlights' => ['toys.deals', 'toys.new_arrivals'],
        ],
        'mother' => [
            'category' => 'mother.category',
            'for_you' => 'mother.for_you',
            'collections' => ['mother.newborn', 'mother.travel', 'mother.comfort', 'mother.organize', 'mother.popular'],
            'spotlights' => ['mother.essentials', 'mother.new_arrivals'],
        ],
        'care' => [
            'category' => 'care.category',
            'for_you' => 'care.for_you',
            'collections' => ['care.gentle', 'care.baby', 'care.bath', 'care.daily_health', 'care.new_arrivals'],
            'spotlights' => ['care.deals', 'care.popular'],
        ],
        'accessories' => [
            'category' => 'accessories.category',
            'for_you' => 'accessories.for_you',
            'collections' => ['accessories.bags', 'accessories.hats', 'accessories.hair', 'accessories.glasses', 'accessories.popular'],
            'spotlights' => ['accessories.today', 'accessories.new_arrivals'],
        ],
        'shoes' => [
            'category' => 'shoes.category',
            'for_you' => 'shoes.for_you',
            'subtabs' => ['shoes.girls', 'shoes.boys'],
            'collections' => [],
            'spotlights' => ['shoes.deals', 'shoes.new_arrivals'],
        ],
    ];

    $themedMarket = match ($activeCategory) {
        'toys' => [
            'panelLabel' => 'فئات الألعاب والتعليم',
            'homeClass' => 'kids-market-home--toys',
            'heroSearch' => 'العاب تعليمية',
            'collections' => collect([
                ['title' => 'آمن للأطفال', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب آمنة للأطفال'],
                ['title' => 'ماركات ألعاب', 'image' => 'kkkk.png', 'search' => 'ماركات ألعاب أطفال'],
                ['title' => 'للرضع والصغار', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'ألعاب الرضع'],
                ['title' => 'تعليم وتفكير', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب تعليمية'],
                ['title' => 'وصل حديثا', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب جديدة'],
            ]),
            'categories' => collect([
                ['name' => 'ألعاب تمثيل', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب تمثيل للأطفال'],
                ['name' => 'الصناديق المفاجئة', 'image' => 'kkkk.png', 'search' => 'صناديق مفاجئة للأطفال'],
                ['name' => 'فنون وحرف', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب فنون وحرف'],
                ['name' => 'تعليم مبكر', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب تعليم مبكر'],
                ['name' => 'ألعاب ما قبل المدرسة', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب ما قبل المدرسة'],
                ['name' => 'مكعبات وتركيب', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'مكعبات تركيب أطفال'],
                ['name' => 'هوايات وتجميع', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب تجميع للأطفال'],
                ['name' => 'ألعاب عائلية', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب عائلية'],
                ['name' => 'دمى وعرائس', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'دمى أطفال'],
                ['name' => 'ألعاب قابلة للجمع', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب قابلة للجمع'],
                ['name' => 'سيارات وتحكم', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'سيارات أطفال ريموت'],
                ['name' => 'ألعاب خارجية', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب خارجية للأطفال'],
                ['name' => 'قراءة وكتابة', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'search' => 'ألعاب قراءة وكتابة'],
                ['name' => 'دمى محشوة', 'image' => 'category_10.jpg', 'search' => 'دمى محشوة للأطفال'],
                ['name' => 'ألغاز وذكاء', 'image' => 'category_2.png', 'search' => 'ألغاز أطفال'],
            ]),
            'spotlights' => collect([
                ['title' => 'عروض الألعاب', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'price' => 16, 'label' => 'عرض ساخن', 'search' => 'عروض ألعاب أطفال'],
                ['title' => 'وصل حديثا', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'price' => 35, 'label' => 'جديد', 'search' => 'ألعاب جديدة'],
            ]),
            'previews' => collect([
                ['title' => 'مجموعة لعب وتمثيل تعليمية للأطفال', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'price' => 53, 'badge' => 'ينمي المهارات', 'discount' => 20],
                ['title' => 'دمية ناعمة للأطفال بتفاصيل لطيفة', 'image' => 'category_10.jpg', 'price' => 55, 'badge' => 'اختيار محبوب', 'discount' => 41],
                ['title' => 'مجموعة لعب للأطفال بألوان مرحة', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'price' => 31, 'badge' => 'جديد', 'discount' => 15],
                ['title' => 'ألعاب تعليم مبكر للقراءة والكتابة', 'image' => 'products/Maileg-Micro Toy Stroller Powder.jpg', 'price' => 28, 'badge' => 'تعليم وترفيه', 'discount' => 18],
            ]),
        ],
        'gifts' => [
            'panelLabel' => 'أفكار هدايا مختارة',
            'homeClass' => 'kids-market-home--gifts',
            'heroSearch' => 'هدايا أطفال',
            'collections' => collect([
                ['title' => 'هدايا آمنة', 'image' => 'category_2.png', 'search' => 'هدايا آمنة للأطفال'],
                ['title' => 'مواليد جدد', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'هدايا مواليد'],
                ['title' => 'هدايا تعليمية', 'image' => 'category_3.png', 'search' => 'هدايا تعليمية للأطفال'],
                ['title' => 'تغليف لطيف', 'image' => 'product-5.jpg', 'search' => 'تغليف هدايا أطفال'],
                ['title' => 'الأكثر طلبا', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'أفضل هدايا أطفال'],
            ]),
            'categories' => collect([
                ['name' => 'هدايا المواليد', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'هدايا المواليد'],
                ['name' => 'هدايا البنات', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'هدايا البنات'],
                ['name' => 'هدايا الأولاد', 'image' => 'Baby Boy Clothes & Outfits.jpg', 'search' => 'هدايا الأولاد'],
                ['name' => 'هدايا تعليمية', 'image' => 'category_3.png', 'search' => 'هدايا تعليمية للأطفال'],
                ['name' => 'دمى ناعمة', 'image' => 'category_10.jpg', 'search' => 'دمى ناعمة هدايا'],
                ['name' => 'أطقم ملابس', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'search' => 'أطقم ملابس هدايا'],
                ['name' => 'هدايا عملية', 'image' => 'Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg', 'search' => 'هدايا عملية للأطفال'],
                ['name' => 'اكسسوارات هدايا', 'image' => 'category_7.png', 'search' => 'اكسسوارات هدايا أطفال'],
                ['name' => 'أحذية هدايا', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'search' => 'أحذية هدايا أطفال'],
                ['name' => 'مجموعات جاهزة', 'image' => 'category_5.png', 'search' => 'مجموعات هدايا أطفال'],
            ]),
            'spotlights' => collect([
                ['title' => 'هدايا اليوم', 'image' => 'category_2.png', 'price' => 18, 'label' => 'عرض لطيف', 'search' => 'عروض هدايا أطفال'],
                ['title' => 'وصل حديثا', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'price' => 42, 'label' => 'جديد', 'search' => 'هدايا أطفال جديدة'],
            ]),
            'previews' => collect([
                ['title' => 'صندوق هدية للمواليد بتفاصيل ناعمة', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'price' => 42, 'badge' => 'هدية جاهزة', 'discount' => 18],
                ['title' => 'طقم أطفال أنيق مناسب للتقديم', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'price' => 35, 'badge' => 'اختيار شائع', 'discount' => 22],
                ['title' => 'دمية ناعمة مع تغليف هدايا بسيط', 'image' => 'category_10.jpg', 'price' => 29, 'badge' => 'محبوب للأطفال', 'discount' => 12],
                ['title' => 'مجموعة تعليمية صغيرة كهدية ذكية', 'image' => 'category_3.png', 'price' => 33, 'badge' => 'تعليم وترفيه', 'discount' => 16],
            ]),
        ],
        'mother' => [
            'panelLabel' => 'أساسيات الأمومة والرضع',
            'homeClass' => 'kids-market-home--mother',
            'heroSearch' => 'مستلزمات الأمومة',
            'collections' => collect([
                ['title' => 'حديثي الولادة', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'مستلزمات حديثي الولادة'],
                ['title' => 'تنقل وخروج', 'image' => 'Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg', 'search' => 'عربات ومستلزمات خروج'],
                ['title' => 'راحة الأم', 'image' => 'category_5.png', 'search' => 'راحة الأمومة'],
                ['title' => 'تنظيم الرضيع', 'image' => 'cart-item-1.jpg', 'search' => 'تنظيم مستلزمات الرضيع'],
                ['title' => 'الأكثر طلبا', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'أفضل مستلزمات الأمومة'],
            ]),
            'categories' => collect([
                ['name' => 'عربات ومقاعد', 'image' => 'Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg', 'search' => 'عربات ومقاعد أطفال'],
                ['name' => 'شنط الأمومة', 'image' => 'category_5.png', 'search' => 'شنط الأمومة'],
                ['name' => 'تجهيز المولود', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'تجهيز المولود'],
                ['name' => 'النوم والسرير', 'image' => 'cart-item-1.jpg', 'search' => 'سرير ومستلزمات نوم الرضيع'],
                ['name' => 'تغذية الرضيع', 'image' => 'category_6.png', 'search' => 'تغذية الرضيع'],
                ['name' => 'استحمام وتغيير', 'image' => 'category_10.jpg', 'search' => 'استحمام وتغيير الطفل'],
                ['name' => 'ملابس الرضع', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'search' => 'ملابس الرضع'],
                ['name' => 'خروج وسفر', 'image' => 'category_2.png', 'search' => 'مستلزمات سفر الطفل'],
                ['name' => 'راحة الأم', 'image' => 'cart-item-2.jpg', 'search' => 'منتجات راحة الأم'],
                ['name' => 'أدوات يومية', 'image' => 'category_7.png', 'search' => 'مستلزمات طفل يومية'],
            ]),
            'spotlights' => collect([
                ['title' => 'أساسيات الرضع', 'image' => 'Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg', 'price' => 58, 'label' => 'عرض مميز', 'search' => 'عروض مستلزمات الرضع'],
                ['title' => 'وصل حديثا', 'image' => 'category_5.png', 'price' => 34, 'label' => 'جديد', 'search' => 'مستلزمات أمومة جديدة'],
            ]),
            'previews' => collect([
                ['title' => 'مجموعة مستلزمات خروج للرضيع والأم', 'image' => 'Strollers, Pushchairs, Prams & Travel systems Archives _ Itty Bitty.jpg', 'price' => 58, 'badge' => 'أساسي يومي', 'discount' => 18],
                ['title' => 'شنطة أمومة عملية بتقسيم داخلي', 'image' => 'category_5.png', 'price' => 34, 'badge' => 'تنظيم أفضل', 'discount' => 12],
                ['title' => 'طقم مولود ناعم مناسب للتجهيز', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'price' => 29, 'badge' => 'ناعم وآمن', 'discount' => 15],
                ['title' => 'مستلزمات عناية يومية للرضيع', 'image' => 'category_6.png', 'price' => 24, 'badge' => 'لطيف للطفل', 'discount' => 10],
            ]),
        ],
        'care' => [
            'panelLabel' => 'الصحة والعناية اليومية',
            'homeClass' => 'kids-market-home--care',
            'heroSearch' => 'الصحة والعناية بالبشرة',
            'collections' => collect([
                ['title' => 'عناية لطيفة', 'image' => 'category_6.png', 'search' => 'عناية لطيفة للأطفال'],
                ['title' => 'للرضع', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'عناية الرضع'],
                ['title' => 'الاستحمام', 'image' => 'category_10.jpg', 'search' => 'استحمام الأطفال'],
                ['title' => 'صحة يومية', 'image' => 'cart-item-3.jpg', 'search' => 'صحة الأطفال اليومية'],
                ['title' => 'وصل حديثا', 'image' => 'category_2.png', 'search' => 'منتجات عناية جديدة'],
            ]),
            'categories' => collect([
                ['name' => 'العناية بالبشرة', 'image' => 'category_6.png', 'search' => 'العناية ببشرة الطفل'],
                ['name' => 'استحمام الطفل', 'image' => 'category_10.jpg', 'search' => 'استحمام الطفل'],
                ['name' => 'عناية الشعر', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'عناية شعر الأطفال'],
                ['name' => 'ترطيب وحماية', 'image' => 'category_2.png', 'search' => 'ترطيب وحماية الطفل'],
                ['name' => 'مناشف وأدوات', 'image' => 'category_5.png', 'search' => 'مناشف وأدوات عناية الطفل'],
                ['name' => 'صحة يومية', 'image' => 'cart-item-3.jpg', 'search' => 'صحة الأطفال اليومية'],
                ['name' => 'للأم والطفل', 'image' => 'cart-item-1.jpg', 'search' => 'عناية الأم والطفل'],
                ['name' => 'منتجات السفر', 'image' => 'category_7.png', 'search' => 'عناية أطفال للسفر'],
                ['name' => 'حديثي الولادة', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'search' => 'عناية حديثي الولادة'],
                ['name' => 'اختيارات آمنة', 'image' => 'category_3.png', 'search' => 'منتجات آمنة للأطفال'],
            ]),
            'spotlights' => collect([
                ['title' => 'عروض العناية', 'image' => 'category_6.png', 'price' => 22, 'label' => 'عرض لطيف', 'search' => 'عروض العناية بالأطفال'],
                ['title' => 'الأكثر طلبا', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'price' => 31, 'label' => 'شائع', 'search' => 'أفضل منتجات عناية أطفال'],
            ]),
            'previews' => collect([
                ['title' => 'مجموعة عناية يومية لطيفة للأطفال', 'image' => 'category_6.png', 'price' => 22, 'badge' => 'لطيف للبشرة', 'discount' => 14],
                ['title' => 'أدوات استحمام ناعمة للرضع', 'image' => 'category_10.jpg', 'price' => 27, 'badge' => 'استحمام آمن', 'discount' => 11],
                ['title' => 'منتجات صحة يومية للطفل', 'image' => 'cart-item-3.jpg', 'price' => 31, 'badge' => 'استخدام يومي', 'discount' => 9],
                ['title' => 'عناية حديثي الولادة بتفاصيل ناعمة', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'price' => 36, 'badge' => 'للرضع', 'discount' => 16],
            ]),
        ],
        'accessories' => [
            'panelLabel' => 'اكسسوارات الأطفال',
            'homeClass' => 'kids-market-home--accessories',
            'heroSearch' => 'اكسسوارات أطفال',
            'collections' => collect([
                ['title' => 'شنط صغيرة', 'image' => 'category_7.png', 'search' => 'شنط أطفال'],
                ['title' => 'قبعات وربطات', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'قبعات وربطات أطفال'],
                ['title' => 'اكسسوارات شعر', 'image' => 'category_1.png', 'search' => 'اكسسوارات شعر أطفال'],
                ['title' => 'نظارات', 'image' => 'category_2.png', 'search' => 'نظارات أطفال'],
                ['title' => 'الأكثر طلبا', 'image' => 'category_9.jpg', 'search' => 'أفضل اكسسوارات أطفال'],
            ]),
            'categories' => collect([
                ['name' => 'شنط أطفال', 'image' => 'category_7.png', 'search' => 'شنط أطفال'],
                ['name' => 'اكسسوارات شعر', 'image' => 'category_1.png', 'search' => 'اكسسوارات شعر أطفال'],
                ['name' => 'قبعات', 'image' => 'Baby Girl Clothes.jpg', 'search' => 'قبعات أطفال'],
                ['name' => 'نظارات', 'image' => 'category_2.png', 'search' => 'نظارات أطفال'],
                ['name' => 'جوارب', 'image' => '5pairs Toddler Girls Cartoon Graphic Socks.jpg', 'search' => 'جوارب أطفال'],
                ['name' => 'أحزمة وربطات', 'image' => 'category_9.jpg', 'search' => 'أحزمة وربطات أطفال'],
                ['name' => 'مجوهرات آمنة', 'image' => 'category_5.png', 'search' => 'مجوهرات أطفال آمنة'],
                ['name' => 'اكسسوارات مواليد', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'search' => 'اكسسوارات مواليد'],
                ['name' => 'اكسسوارات أحذية', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'search' => 'اكسسوارات أحذية أطفال'],
                ['name' => 'هدايا اكسسوارات', 'image' => 'category_3.png', 'search' => 'هدايا اكسسوارات أطفال'],
            ]),
            'spotlights' => collect([
                ['title' => 'اكسسوارات اليوم', 'image' => 'category_7.png', 'price' => 18, 'label' => 'عرض سريع', 'search' => 'عروض اكسسوارات أطفال'],
                ['title' => 'وصل حديثا', 'image' => 'category_1.png', 'price' => 26, 'label' => 'جديد', 'search' => 'اكسسوارات أطفال جديدة'],
            ]),
            'previews' => collect([
                ['title' => 'شنطة أطفال صغيرة بتفاصيل مرحة', 'image' => 'category_7.png', 'price' => 18, 'badge' => 'لطيفة للخروج', 'discount' => 12],
                ['title' => 'مجموعة ربطات شعر وقبعات للأطفال', 'image' => 'Baby Girl Clothes.jpg', 'price' => 26, 'badge' => 'إطلالة كاملة', 'discount' => 18],
                ['title' => 'جوارب كرتونية ناعمة متعددة', 'image' => '5pairs Toddler Girls Cartoon Graphic Socks.jpg', 'price' => 12, 'badge' => 'منتج رائج', 'discount' => 8],
                ['title' => 'اكسسوارات مواليد ناعمة وآمنة', 'image' => 'Cute and Adorable Babies with Cute Smile.jpg', 'price' => 20, 'badge' => 'للرضع', 'discount' => 10],
            ]),
        ],
        'shoes' => [
            'panelLabel' => 'أحذية الأطفال',
            'homeClass' => 'kids-market-home--shoes',
            'heroSearch' => 'أحذية أطفال',
            'subTabs' => collect([
                ['label' => 'بناتي', 'active' => true, 'search' => 'أحذية بنات'],
                ['label' => 'ولادي', 'active' => false, 'search' => 'أحذية أولاد'],
            ]),
            'collections' => collect(),
            'categories' => collect([
                ['name' => 'صنادل بناتي', 'image' => 'products/Flower print sandals for baby girls.jpg', 'search' => 'صنادل بنات'],
                ['name' => 'أحذية ولادي', 'image' => 'products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg', 'search' => 'أحذية أولاد'],
                ['name' => 'أحذية رضع', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'search' => 'أحذية رضع'],
                ['name' => 'بوت أطفال', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'search' => 'بوت أطفال'],
                ['name' => 'أحذية مشي', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'search' => 'أحذية مشي أطفال'],
                ['name' => 'سنيكرز', 'image' => 'products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg', 'search' => 'سنيكرز أطفال'],
                ['name' => 'أحذية حفلات', 'image' => 'products/Flower print sandals for baby girls.jpg', 'search' => 'أحذية حفلات بنات'],
                ['name' => 'شباشب مريحة', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'search' => 'شباشب أطفال'],
                ['name' => 'أحذية مدرسية', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'search' => 'أحذية مدرسية أطفال'],
                ['name' => 'جوارب وأحذية', 'image' => '5pairs Toddler Girls Cartoon Graphic Socks.jpg', 'search' => 'جوارب وأحذية أطفال'],
                ['name' => 'أحذية خارجية', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'search' => 'أحذية خارجية أطفال'],
                ['name' => 'أحذية ناعمة', 'image' => 'products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg', 'search' => 'أحذية ناعمة أطفال'],
            ]),
            'spotlights' => collect([
                ['title' => 'عروض الأحذية', 'image' => 'products/Flower print sandals for baby girls.jpg', 'price' => 42, 'label' => 'عرض سريع', 'search' => 'عروض أحذية أطفال'],
                ['title' => 'وصل حديثا', 'image' => 'products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg', 'price' => 31, 'label' => 'جديد', 'search' => 'أحذية أطفال جديدة'],
            ]),
            'previews' => collect([
                ['title' => 'صندل بناتي بطبعة زهور مريح للخروج', 'image' => 'products/Flower print sandals for baby girls.jpg', 'price' => 42, 'badge' => '# اختيار بناتي', 'discount' => 18],
                ['title' => 'حذاء ولادي ناعم مناسب للخطوات الأولى', 'image' => 'products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg', 'price' => 31, 'badge' => 'مريح للرضع', 'discount' => 12],
                ['title' => 'أحذية مشي خفيفة للطلعات اليومية', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'price' => 19, 'badge' => 'خفيف ومرن', 'discount' => 10],
                ['title' => 'بوت أطفال دافئ مقاوم للانزلاق', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'price' => 44, 'badge' => 'أفضل مبيعات', 'discount' => 16],
            ]),
        ],
        default => null,
    };

    $generalFallbackProducts = $marketProducts
        ->merge($offerProducts)
        ->merge($featuredProducts)
        ->merge($latestProducts)
        ->unique('id')
        ->values();

    $newFallbackProducts = collect($latestProducts)
        ->merge($featuredProducts)
        ->unique('id')
        ->values();

    $dealFallbackProducts = collect($offerProducts)
        ->merge(collect($latestProducts)->filter(fn ($product) => (float) ($product->sale_price ?? 0) > 0))
        ->merge($featuredProducts)
        ->unique('id')
        ->values();

    $bestsellerFallbackProducts = collect($featuredProducts)
        ->merge($latestProducts)
        ->merge($offerProducts)
        ->unique('id')
        ->sortByDesc(fn ($product) => (int) ($product->active_reviews_count ?? 0) + ((bool) ($product->featured ?? false) ? 1000 : 0))
        ->values();

    $forYouProducts = $assignedProductsForSection('home.for_you', 16);
    $newProducts = $assignedProductsForSection('home.new', 16);
    $dealFeedProducts = $assignedProductsForSection('home.offers', 16);
    $bestsellerProducts = $assignedProductsForSection('home.bestsellers', 16);
    $kidsForYouProducts = $assignedProductsForSection('kids.for_you', 16);
    $kidsCollectionProducts = $kidsCollections->mapWithKeys(
        fn ($collection) => [$collection['id'] => $assignedProductsForSection($collection['section'], 16)]
    );

    $previewProducts = [
        'for-you' => collect([
            ['title' => 'طقم أطفال صيفي مريح بتفاصيل مرحة', 'image' => 'Baby Girl Clothes.jpg', 'price' => 38, 'badge' => '# اختيار شائع', 'discount' => 18],
            ['title' => 'حذاء أطفال خفيف للطلعات اليومية', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'price' => 44, 'badge' => 'مناسب لك', 'discount' => 12],
            ['title' => 'جوارب كرتونية ناعمة للأطفال', 'image' => '5pairs Toddler Girls Cartoon Graphic Socks.jpg', 'price' => 12, 'badge' => 'منتج رائج', 'discount' => 8],
            ['title' => 'طقم مولود مع قبعة قطنية', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'price' => 29, 'badge' => 'مختار لك', 'discount' => 15],
        ]),
        'new-in' => collect([
            ['title' => 'وصل حديثا: رومبر بناتي بطبعة فراولة', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'price' => 31, 'badge' => 'جديد', 'discount' => 10],
            ['title' => 'مجموعة أحذية منزلية ناعمة للرضع', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'price' => 19, 'badge' => 'وصل حديثا', 'discount' => 6],
            ['title' => 'طقم Carter للمواليد بتصميم يومي', 'image' => "Carter's baby-girls 2 Piece Shortall Set 121g499.jpg", 'price' => 35, 'badge' => 'جديد', 'discount' => 9],
            ['title' => 'إطلالة أطفال خفيفة للمشاوير', 'image' => 'Baby Boy Clothes & Outfits.jpg', 'price' => 42, 'badge' => 'وصل حديثا', 'discount' => 11],
        ]),
        'deals' => collect([
            ['title' => 'عرض خاص على أحذية أطفال شتوية', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'price' => 39, 'badge' => 'عرض اليوم', 'discount' => 30],
            ['title' => 'خصم على جوارب كرتونية متعددة', 'image' => '5pairs Toddler Girls Cartoon Graphic Socks.jpg', 'price' => 10, 'badge' => 'Flash Sale', 'discount' => 25],
            ['title' => 'تخفيض على طقم مولود كامل', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'price' => 24, 'badge' => 'عرض محدود', 'discount' => 22],
            ['title' => 'حذاء مشي أول للرضع بسعر خاص', 'image' => 'Baby Sock Shoes Baby Walking Shoes Infant House Slippers Boys & Girls Non-slip First Walk Sneakers｜Temu.jpg', 'price' => 16, 'badge' => 'Deal', 'discount' => 18],
        ]),
        'bestsellers' => collect([
            ['title' => 'الأكثر طلبا: ملابس بنات يومية', 'image' => 'Baby Girl Clothes.jpg', 'price' => 37, 'badge' => '#1 أفضل مبيعات', 'discount' => 14],
            ['title' => 'أحذية أطفال مفضلة للعملاء', 'image' => 'Autumn_Winter Children Boots Boys Girls Leather Martin Boots Plush Fashion Waterproof Non-slip Warm Kids Boots Shoes 21-36 210308.jpg', 'price' => 46, 'badge' => '#2 أفضل مبيعات', 'discount' => 10],
            ['title' => 'جوارب ناعمة عالية التكرار', 'image' => '5pairs Toddler Girls Cartoon Graphic Socks.jpg', 'price' => 13, 'badge' => 'عملاء متكررون', 'discount' => 7],
            ['title' => 'طقم مولود محبوب للأهداء', 'image' => 'Baby Girl Strawberry Printed Patchwork Romper With Bowknot And Lace Trim, And Hat.jpg', 'price' => 28, 'badge' => 'Top Rated', 'discount' => 12],
        ]),
    ];

    $productPanels = collect([
        ['id' => 'for-you', 'label' => 'من أجلك', 'icon' => null, 'products' => $forYouProducts],
        ['id' => 'new-in', 'label' => 'الجديد', 'icon' => 'bi-stars', 'products' => $newProducts],
        ['id' => 'deals', 'label' => 'العروض والعروض السريعة', 'icon' => 'bi-tag-fill', 'products' => $dealFeedProducts],
        ['id' => 'bestsellers', 'label' => 'أفضل المبيعات', 'icon' => 'bi-trophy-fill', 'products' => $bestsellerProducts],
    ]);

    $homeCategories = $categoriesForContext('home.all', $categories);

    $kidsCategoryPanels = $kidsAgeTabs->map(function ($tab) use ($categoriesForContext, $kidsCategoryFallbacks) {
        return [
            ...$tab,
            'categories' => $categoriesForContext($tab['context'], $kidsCategoryFallbacks->get($tab['context'], collect())),
        ];
    });

    $kidsProductPanels = collect([
        ['id' => 'for-you', 'label' => 'من أجلك', 'products' => $kidsForYouProducts, 'preview' => $previewProducts['for-you']],
    ])->merge($kidsCollections->map(fn ($collection) => [
        'id' => $collection['id'],
        'label' => $collection['title'],
        'products' => $kidsCollectionProducts->get($collection['id'], collect()),
        'preview' => $previewProducts['for-you'],
    ]));

    $themeConfig = $themeSections[$activeCategory] ?? null;
    $themeCollections = collect();
    $themeSubTabs = collect();
    $themeSpotlights = collect();
    $themeCategories = collect();
    $themeProductPanels = collect();

    if ($isThemedMarket && $themedMarket && $themeConfig) {
        $themeCategories = $categoriesForContext($themeConfig['category'], $themedMarket['categories']);

        $themeCollections = collect($themedMarket['collections'] ?? [])->values()->map(function ($collection, $index) use ($themeConfig) {
            $section = $themeConfig['collections'][$index] ?? null;

            return array_merge($collection, [
                'id' => 'collection-'.$index,
                'section' => $section,
            ]);
        })->filter(fn ($collection) => ! empty($collection['section']))->values();

        $themeSubTabs = collect($themedMarket['subTabs'] ?? [])->values()->map(function ($subTab, $index) use ($themeConfig) {
            $section = $themeConfig['subtabs'][$index] ?? null;

            return array_merge($subTab, [
                'id' => 'subtab-'.$index,
                'section' => $section,
            ]);
        })->filter(fn ($subTab) => ! empty($subTab['section']))->values();

        $themeSpotlights = collect($themedMarket['spotlights'] ?? [])->values()->map(function ($spotlight, $index) use ($themeConfig) {
            $section = $themeConfig['spotlights'][$index] ?? null;

            return array_merge($spotlight, [
                'id' => 'spotlight-'.$index,
                'section' => $section,
            ]);
        })->filter(fn ($spotlight) => ! empty($spotlight['section']))->values();

        $themeProductPanels = collect([
            [
                'id' => 'for-you',
                'label' => 'من أجلك',
                'products' => $assignedProductsForSection($themeConfig['for_you'], 16),
                'preview' => $themedMarket['previews'],
            ],
        ])
            ->merge($themeSubTabs->map(fn ($subTab) => [
                'id' => $subTab['id'],
                'label' => $subTab['label'],
                'products' => $assignedProductsForSection($subTab['section'], 16),
                'preview' => $themedMarket['previews'],
            ]))
            ->merge($themeCollections->map(fn ($collection) => [
                'id' => $collection['id'],
                'label' => $collection['title'],
                'products' => $assignedProductsForSection($collection['section'], 16),
                'preview' => $themedMarket['previews'],
            ]))
            ->merge($themeSpotlights->map(fn ($spotlight) => [
                'id' => $spotlight['id'],
                'label' => $spotlight['title'],
                'products' => $assignedProductsForSection($spotlight['section'], 16),
                'preview' => $themedMarket['previews'],
            ]));
    }
@endphp

<div class="kids-market-home {{ $isKids ? 'kids-market-home--kids' : ($isThemedMarket && $themedMarket ? $themedMarket['homeClass'] : 'kids-market-home--all') }}" dir="rtl">
    <x-marketing.home-tab-promo-cards :campaigns="$promotions['home_tab_promo_cards'] ?? collect()" />
    <x-marketing.home-tab-circle-items :campaigns="$promotions['home_tab_circle_categories'] ?? collect()" />

    @if($isKids)
        <section class="kids-market-panel kids-market-kids-panel" aria-label="تقسيمات الأطفال حسب العمر">
            <nav class="kids-market-age-tabs" aria-label="أعمار الأطفال" data-kids-category-tabs>
                @foreach($kidsCategoryPanels as $tab)
                    <button
                        type="button"
                        class="kids-market-age-tab {{ $tab['active'] ? 'is-active' : '' }}"
                        data-kids-category-tab="{{ $tab['id'] }}"
                        aria-controls="kids-category-panel-{{ $tab['id'] }}"
                        aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                    >
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>

            @foreach($kidsCategoryPanels as $tab)
                <div
                    id="kids-category-panel-{{ $tab['id'] }}"
                    class="kids-market-kids-grid"
                    data-kids-category-panel="{{ $tab['id'] }}"
                    @if(! $tab['active']) hidden @endif
                >
                    @foreach($tab['categories'] as $category)
                        @php($categoryItem = $displayCategory($category))
                        <a class="kids-market-kid-category" href="{{ $categoryItem['url'] }}">
                            <span class="kids-market-kid-category__img">
                                <img
                                    src="{{ $categoryItem['image'] }}"
                                    alt="{{ $categoryItem['name'] }}"
                                    loading="lazy"
                                    onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                                >
                            </span>
                            <span class="kids-market-kid-category__name">{{ $categoryItem['name'] }}</span>
                            @if($categoryItem['age'])
                                <span class="kids-market-kid-category__age">{{ $categoryItem['age'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </section>

        <section class="kids-market-kids-collections" aria-label="مجموعات أطفال مختارة" data-kids-product-tabs>
            @foreach($kidsCollections as $collection)
                <button
                    type="button"
                    class="kids-market-kids-tile"
                    data-kids-product-tab="{{ $collection['id'] }}"
                    aria-controls="kids-product-panel-{{ $collection['id'] }}"
                    aria-selected="false"
                >
                    <img
                        src="{{ $resolveStaticImage($collection['image']) }}"
                        alt="{{ $collection['title'] }}"
                        loading="lazy"
                        onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                    >
                    <span>{{ $collection['title'] }}</span>
                </button>
            @endforeach
        </section>

        <h2 class="kids-market-section-title" data-kids-products-heading>{{ $kidsProductPanels->first()['label'] }}</h2>

        <div class="kids-market-panels kids-market-panels--kids">
            @foreach($kidsProductPanels as $panel)
                <section
                    id="kids-product-panel-{{ $panel['id'] }}"
                    class="kids-market-feed kids-market-feed--kids"
                    data-kids-product-panel="{{ $panel['id'] }}"
                    aria-label="{{ $panel['label'] }}"
                    @if(! $loop->first) hidden @endif
                >
                    @forelse($panel['products'] as $product)
                        <x-shop.market-product-card :product="$product" />
                    @empty
                        <div class="kids-market-empty-state">لم يتم تخصيص منتجات لهذا القسم بعد.</div>
                    @endforelse
                </section>
            @endforeach
        </div>

    @elseif($isThemedMarket && $themedMarket)
        @if($themeCollections->isNotEmpty())
            <section class="kids-market-kids-collections kids-market-kids-collections--theme" aria-label="مجموعات مختارة" data-theme-product-tabs>
                @foreach($themeCollections as $collection)
                    <button
                        type="button"
                        class="kids-market-kids-tile kids-market-theme-tile"
                        data-theme-product-tab="{{ $collection['id'] }}"
                        aria-controls="theme-product-panel-{{ $collection['id'] }}"
                        aria-selected="false"
                    >
                        <img
                            src="{{ $resolveStaticImage($collection['image']) }}"
                            alt="{{ $collection['title'] }}"
                            loading="lazy"
                            onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                        >
                        <span>{{ $collection['title'] }}</span>
                    </button>
                @endforeach
            </section>
        @endif

        <section class="kids-market-panel kids-market-theme-panel" aria-label="{{ $themedMarket['panelLabel'] }}">
            @if($themeSubTabs->isNotEmpty())
                <nav class="kids-market-theme-subtabs" aria-label="تقسيم الأحذية" data-theme-product-tabs>
                    @foreach($themeSubTabs as $subTab)
                        <button
                            type="button"
                            class="kids-market-theme-subtab {{ $subTab['active'] ? 'is-active' : '' }}"
                            data-theme-product-tab="{{ $subTab['id'] }}"
                            aria-controls="theme-product-panel-{{ $subTab['id'] }}"
                            aria-selected="{{ $subTab['active'] ? 'true' : 'false' }}"
                        >
                            {{ $subTab['label'] }}
                        </button>
                    @endforeach
                </nav>
            @endif

            <div class="kids-market-theme-grid">
                @foreach($themeCategories as $category)
                    @php($categoryItem = $displayCategory($category))
                    <a class="kids-market-kid-category kids-market-theme-category" href="{{ $categoryItem['url'] }}">
                        <span class="kids-market-kid-category__img">
                            <img
                                src="{{ $categoryItem['image'] }}"
                                alt="{{ $categoryItem['name'] }}"
                                loading="lazy"
                                onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                            >
                        </span>
                        <span class="kids-market-kid-category__name">{{ $categoryItem['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="kids-market-theme-spotlights" aria-label="عروض مختارة" data-theme-product-tabs>
            @foreach($themeSpotlights as $spotlight)
                <button
                    type="button"
                    class="kids-market-theme-spotlight"
                    data-theme-product-tab="{{ $spotlight['id'] }}"
                    aria-controls="theme-product-panel-{{ $spotlight['id'] }}"
                    aria-selected="false"
                >
                    <strong>{{ $spotlight['title'] }}</strong>
                    <img
                        src="{{ $resolveStaticImage($spotlight['image']) }}"
                        alt="{{ $spotlight['title'] }}"
                        loading="lazy"
                        onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                    >
                    <span><small>ر.ي</small>{{ number_format($spotlight['price'], 0) }}</span>
                    <em>{{ $spotlight['label'] }}</em>
                </button>
            @endforeach
        </section>

        <h2 class="kids-market-section-title" data-theme-products-heading>{{ $themeProductPanels->first()['label'] ?? 'من أجلك' }}</h2>

        <div class="kids-market-panels kids-market-panels--theme">
            @foreach($themeProductPanels as $panel)
                <section
                    id="theme-product-panel-{{ $panel['id'] }}"
                    class="kids-market-feed kids-market-feed--theme"
                    data-theme-product-panel="{{ $panel['id'] }}"
                    aria-label="{{ $panel['label'] }}"
                    @if(! $loop->first) hidden @endif
                >
                    @forelse($panel['products'] as $product)
                        <x-shop.market-product-card :product="$product" />
                    @empty
                        <div class="kids-market-empty-state">لم يتم تخصيص منتجات لهذا القسم بعد.</div>
                    @endforelse
                </section>
            @endforeach
        </div>
    @else
        <section class="kids-market-panel kids-market-category-panel">
            <div class="kids-market-category-grid">
                @forelse($homeCategories as $category)
                    <a class="kids-market-category" href="{{ route('shop.category', $category->slug) }}">
                        <span class="kids-market-category-img">
                            <img
                                src="{{ $resolveImage($category->image, 'categories') }}"
                                alt="{{ $category->name }}"
                                loading="lazy"
                                onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                            >
                        </span>
                        <span>{{ $category->name }}</span>
                    </a>
                @empty
                    @foreach($fallbackCategories as $category)
                        <a class="kids-market-category" href="{{ $category['url'] }}">
                            <span class="kids-market-category-img">
                                <img
                                    src="{{ $resolveStaticImage($category['image']) }}"
                                    alt="{{ $category['name'] }}"
                                    loading="lazy"
                                    onerror="this.src='{{ asset('assets/images/home/demo3/category_1.png') }}'"
                                >
                            </span>
                            <span>{{ $category['name'] }}</span>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </section>

        <nav class="kids-market-category-tabs" aria-label="تصفية المنتجات" data-market-tabs>
            @foreach($productPanels as $panel)
                <button
                    type="button"
                    class="kids-market-category-tab {{ $loop->first ? 'is-active' : '' }}"
                    data-market-tab="{{ $panel['id'] }}"
                    aria-controls="market-panel-{{ $panel['id'] }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                >
                    @if(! empty($panel['icon']))
                        <i class="bi {{ $panel['icon'] }}" aria-hidden="true"></i>
                    @endif
                    <span>{{ $panel['label'] }}</span>
                </button>
            @endforeach
        </nav>

        <h2 class="kids-market-section-title" data-market-heading>{{ $productPanels->first()['label'] }}</h2>

        <div class="kids-market-panels">
            @foreach($productPanels as $panel)
                <section
                    id="market-panel-{{ $panel['id'] }}"
                    class="kids-market-feed"
                    data-market-panel="{{ $panel['id'] }}"
                    aria-label="{{ $panel['label'] }}"
                    @if(! $loop->first) hidden @endif
                >
                    @forelse($panel['products'] as $product)
                        <x-shop.market-product-card :product="$product" />
                    @empty
                        <div class="kids-market-empty-state">لم يتم تخصيص منتجات لهذا القسم بعد.</div>
                    @endforelse
                </section>
            @endforeach
        </div>
    @endif
    <x-marketing.home-tab-bottom-banner :campaigns="$promotions['home_tab_bottom_banner'] ?? collect()" />
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-market-tabs]').forEach(function (tabs) {
                    const home = tabs.closest('.kids-market-home');

                    if (!home) {
                        return;
                    }

                    const buttons = Array.from(tabs.querySelectorAll('[data-market-tab]'));
                    const panels = Array.from(home.querySelectorAll('[data-market-panel]'));
                    const heading = home.querySelector('[data-market-heading]');

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            const target = button.dataset.marketTab;

                            buttons.forEach(function (item) {
                                const isActive = item === button;
                                item.classList.toggle('is-active', isActive);
                                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                            });

                            panels.forEach(function (panel) {
                                panel.hidden = panel.dataset.marketPanel !== target;
                            });

                            if (heading) {
                                heading.textContent = button.textContent.trim();
                            }
                        });
                    });
                });

                document.querySelectorAll('[data-kids-category-tabs]').forEach(function (tabs) {
                    const home = tabs.closest('.kids-market-home');

                    if (!home) {
                        return;
                    }

                    const buttons = Array.from(tabs.querySelectorAll('[data-kids-category-tab]'));
                    const panels = Array.from(home.querySelectorAll('[data-kids-category-panel]'));

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            const target = button.dataset.kidsCategoryTab;

                            buttons.forEach(function (item) {
                                const isActive = item === button;
                                item.classList.toggle('is-active', isActive);
                                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                            });

                            panels.forEach(function (panel) {
                                panel.hidden = panel.dataset.kidsCategoryPanel !== target;
                            });
                        });
                    });
                });

                document.querySelectorAll('[data-kids-product-tabs]').forEach(function (tabs) {
                    const home = tabs.closest('.kids-market-home');

                    if (!home) {
                        return;
                    }

                    const buttons = Array.from(tabs.querySelectorAll('[data-kids-product-tab]'));
                    const panels = Array.from(home.querySelectorAll('[data-kids-product-panel]'));
                    const heading = home.querySelector('[data-kids-products-heading]');

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            const target = button.dataset.kidsProductTab;

                            buttons.forEach(function (item) {
                                const isActive = item === button;
                                item.classList.toggle('is-active', isActive);
                                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                            });

                            panels.forEach(function (panel) {
                                panel.hidden = panel.dataset.kidsProductPanel !== target;
                            });

                            if (heading) {
                                heading.textContent = button.textContent.trim();
                            }
                        });
                    });
                });

                document.querySelectorAll('.kids-market-home').forEach(function (home) {
                    if (!home.querySelector('[data-theme-product-tabs]')) {
                        return;
                    }

                    const buttons = Array.from(home.querySelectorAll('[data-theme-product-tab]'));
                    const panels = Array.from(home.querySelectorAll('[data-theme-product-panel]'));
                    const heading = home.querySelector('[data-theme-products-heading]');

                    buttons.forEach(function (button) {
                        button.addEventListener('click', function () {
                            const target = button.dataset.themeProductTab;

                            buttons.forEach(function (item) {
                                const isActive = item === button;
                                item.classList.toggle('is-active', isActive);
                                item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                            });

                            panels.forEach(function (panel) {
                                panel.hidden = panel.dataset.themeProductPanel !== target;
                            });

                            if (heading) {
                                heading.textContent = button.textContent.trim().replace(/\s+/g, ' ');
                            }
                        });
                    });
                });
            });
        </script>
    @endpush
@endonce
