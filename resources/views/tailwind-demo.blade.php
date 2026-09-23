<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.dunya_alatfaal') }} - عرض تجريبي</title>
    <!-- Google Fonts - Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #F3F4F6;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-8 px-4">

    <!-- Mobile Device Simulator Container -->
    <div class="w-full max-w-md bg-[#FDFBF7] rounded-[40px] shadow-2xl border-[10px] border-slate-900 overflow-hidden relative" style="height: 844px; max-height: 90vh;">
        <!-- Simulated Speaker/Camera Notch -->
        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 h-5 w-32 bg-slate-900 rounded-b-xl z-50 flex items-center justify-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-700"></span>
            <span class="w-10 h-1 bg-slate-800 rounded-full"></span>
        </div>

        <!-- Simulator Screen Content -->
        <div class="w-full h-full overflow-y-auto pt-6 pb-8 scrollbar-none bg-[#FDFBF7]">
            
            <!-- Use the newly created component -->
            <x-shop.top-header-banner :banner="$banner" activeCategory="all" />

            <!-- Additional Mock Content below the component to make the page scrollable and look like a real app -->
            <div class="px-4 mt-6">
                <!-- Section Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black text-slate-800">التصنيفات المميزة</h3>
                    <a href="{{ route('categories.index') }}" class="text-xs text-[#FF8C61] font-bold hover:underline">عرض الكل</a>
                </div>

                <!-- Grid Categories -->
                <div class="grid grid-cols-4 gap-3.5">
                    @php
                        $mockCats = [
                            ['name' => 'فساتين بنات', 'img' => 'Baby Boy Sets.jpg'],
                            ['name' => 'ألعاب خشبية', 'img' => 'Maileg-Micro Toy Stroller Powder.jpg'],
                            ['name' => 'جوارب ناعمة', 'img' => '1_66US $ _2016 Christening Baptism Winter Warm Meia Infantil Cotton Baby Socks,kid Ruffled Calzini Neonato Knitted Knee Lace Newborn Socks - Socks - AliExpress.jpg'],
                            ['name' => 'أحذية لطيفة', 'img' => 'Flower print sandals for baby girls.jpg'],
                        ];
                    @endphp
                    @foreach($mockCats as $mc)
                        <div class="flex flex-col items-center group cursor-pointer">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-[#F5EFE6] shadow-sm flex items-center justify-center overflow-hidden group-hover:-translate-y-1 transition-transform duration-300">
                                <img src="{{ asset('uploads/products/' . $mc['img']) }}" alt="{{ $mc['name'] }}" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[9px] font-bold text-gray-500 mt-1.5 text-center leading-tight truncate w-full">{{ $mc['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Flash Sale Section -->
            <div class="px-4 mt-8">
                <div class="bg-gradient-to-l from-[#FF8C61]/15 to-[#FFE17D]/10 rounded-[20px] p-4 border border-[#FF8C61]/10">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-1.5">
                            <span class="bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md">عروض حصرية</span>
                            <h3 class="text-xs font-black text-slate-800">صفقات نهاية الأسبوع</h3>
                        </div>
                        <div class="flex items-center gap-1 text-[10px] text-gray-500 font-bold">
                            <span>تنتهي في:</span>
                            <span class="bg-slate-800 text-white px-1 py-0.5 rounded">05</span>:
                            <span class="bg-slate-800 text-white px-1 py-0.5 rounded">42</span>:
                            <span class="bg-slate-800 text-white px-1 py-0.5 rounded">17</span>
                        </div>
                    </div>

                    <!-- Products Row -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white rounded-xl p-2 border border-[#F5EFE6] shadow-sm relative group cursor-pointer">
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded-md z-10">-30%</span>
                            <div class="w-full aspect-square bg-[#FDFBF7] rounded-lg overflow-hidden mb-2">
                                <img src="{{ asset('uploads/products/Baby Polka Dot 3D Ear Design Hooded Belted Sleep Robe.jpg') }}" alt="روب أطفال" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <h4 class="text-[10px] font-bold text-slate-800 truncate mb-1">روب استحمام ناعم للأطفال</h4>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-extrabold text-[#FF8C61]">9,400 ر.ي</span>
                                <span class="text-[8px] text-gray-400 line-through">13,500 ر.ي</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl p-2 border border-[#F5EFE6] shadow-sm relative group cursor-pointer">
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded-md z-10">-25%</span>
                            <div class="w-full aspect-square bg-[#FDFBF7] rounded-lg overflow-hidden mb-2">
                                <img src="{{ asset('uploads/products/Mamas & Papas Unisex Baby Bear Booties - Sand.jpg') }}" alt="حذاء أطفال" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <h4 class="text-[10px] font-bold text-slate-800 truncate mb-1">حذاء الدب اللطيف لحديثي الولادة</h4>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-extrabold text-[#FF8C61]">5,200 ر.ي</span>
                                <span class="text-[8px] text-gray-400 line-through">7,000 ر.ي</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Space -->
            <div class="h-12"></div>
        </div>

        <!-- Simulated Home Bar for iOS mockup -->
        <div class="absolute bottom-1.5 left-1/2 transform -translate-x-1/2 h-1 w-32 bg-slate-900 rounded-full z-50"></div>
    </div>

</body>
</html>
