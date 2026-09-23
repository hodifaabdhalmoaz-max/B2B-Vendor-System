<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:performance {--force : Force optimization even in development}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application performance for production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء تحسين أداء التطبيق...');
        $this->newLine();

        // التحقق من البيئة
        if (!$this->option('force') && !app()->environment('production')) {
            if (!$this->confirm('أنت لست في بيئة الإنتاج. هل تريد المتابعة؟')) {
                $this->info('تم إلغاء العملية.');
                return Command::SUCCESS;
            }
        }

        $startTime = microtime(true);

        try {
            // تحسين Laravel
            $this->optimizeLaravel();

            // تحسين قاعدة البيانات
            $this->optimizeDatabase();

            // تحسين التخزين المؤقت
            $this->optimizeCache();

            // تحسين الملفات
            $this->optimizeFiles();

            // تحسين الصور
            $this->optimizeImages();

            // تنظيف الملفات المؤقتة
            $this->cleanupTempFiles();

            $endTime = microtime(true);
            $duration = round(($endTime - $startTime), 2);

            $this->newLine();
            $this->info("✅ تم تحسين الأداء بنجاح في {$duration} ثانية!");
            
            // عرض إحصائيات الأداء
            $this->displayPerformanceStats();

        } catch (\Exception $e) {
            $this->error("❌ فشل في تحسين الأداء: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * تحسين Laravel
     */
    protected function optimizeLaravel(): void
    {
        $this->info('🔧 تحسين Laravel...');

        $commands = [
            'config:cache' => 'تخزين إعدادات التطبيق مؤقتاً',
            'route:cache' => 'تخزين المسارات مؤقتاً',
            'view:cache' => 'تخزين القوالب مؤقتاً',
            'event:cache' => 'تخزين الأحداث مؤقتاً',
        ];

        foreach ($commands as $command => $description) {
            $this->line("  - {$description}");
            Artisan::call($command);
        }

        // تحسين Composer autoloader
        if (app()->environment('production')) {
            $this->line('  - تحسين Composer autoloader');
            exec('composer dump-autoload --optimize --no-dev');
        }
    }

    /**
     * تحسين قاعدة البيانات
     */
    protected function optimizeDatabase(): void
    {
        $this->info('🗄️ تحسين قاعدة البيانات...');

        try {
            // تحليل الجداول
            $this->line('  - تحليل الجداول');
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                DB::statement("ANALYZE TABLE {$tableName}");
            }

            // تحسين الجداول
            $this->line('  - تحسين الجداول');
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                DB::statement("OPTIMIZE TABLE {$tableName}");
            }

            // تشغيل migrations إذا لزم الأمر
            $this->line('  - التحقق من migrations');
            Artisan::call('migrate', ['--force' => true]);

        } catch (\Exception $e) {
            $this->warn("تحذير: فشل في تحسين قاعدة البيانات - " . $e->getMessage());
        }
    }

    /**
     * تحسين التخزين المؤقت
     */
    protected function optimizeCache(): void
    {
        $this->info('💾 تحسين التخزين المؤقت...');

        // مسح الكاش القديم
        $this->line('  - مسح الكاش القديم');
        Artisan::call('cache:clear');

        // إعادة بناء الكاش
        $this->line('  - إعادة بناء الكاش');
        
        // تخزين البيانات الأساسية مؤقتاً
        $this->warmupCache();
    }

    /**
     * تسخين الكاش
     */
    protected function warmupCache(): void
    {
        try {
            // تخزين الإعدادات مؤقتاً
            Cache::remember('app_settings', 86400, function () {
                return [
                    'app_name' => config('app.name'),
                    'app_url' => config('app.url'),
                    'timezone' => config('app.timezone'),
                ];
            });

            // تخزين الفئات مؤقتاً
            if (class_exists(\App\Models\Category::class)) {
                Cache::remember('active_categories', 3600, function () {
                    return \App\Models\Category::where('status', 1)->get();
                });
            }

            // تخزين العلامات التجارية مؤقتاً
            if (class_exists(\App\Models\Brand::class)) {
                Cache::remember('active_brands', 3600, function () {
                    return \App\Models\Brand::where('status', 1)->get();
                });
            }

        } catch (\Exception $e) {
            $this->warn("تحذير: فشل في تسخين الكاش - " . $e->getMessage());
        }
    }

    /**
     * تحسين الملفات
     */
    protected function optimizeFiles(): void
    {
        $this->info('📁 تحسين الملفات...');

        // ضغط ملفات CSS و JS
        if (app()->environment('production')) {
            $this->line('  - ضغط ملفات CSS و JS');
            $this->compressAssets();
        }

        // تنظيف ملفات السجلات القديمة
        $this->line('  - تنظيف ملفات السجلات القديمة');
        $this->cleanupLogs();
    }

    /**
     * ضغط الأصول
     */
    protected function compressAssets(): void
    {
        $publicPath = public_path();
        
        // ضغط ملفات CSS
        $cssFiles = File::glob($publicPath . '/assets/css/*.css');
        foreach ($cssFiles as $file) {
            if (!str_contains($file, '.min.')) {
                $content = File::get($file);
                $compressed = $this->minifyCss($content);
                $minFile = str_replace('.css', '.min.css', $file);
                File::put($minFile, $compressed);
            }
        }

        // ضغط ملفات JS
        $jsFiles = File::glob($publicPath . '/assets/js/*.js');
        foreach ($jsFiles as $file) {
            if (!str_contains($file, '.min.')) {
                $content = File::get($file);
                $compressed = $this->minifyJs($content);
                $minFile = str_replace('.js', '.min.js', $file);
                File::put($minFile, $compressed);
            }
        }
    }

    /**
     * ضغط CSS
     */
    protected function minifyCss(string $css): string
    {
        // إزالة التعليقات
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // إزالة المسافات الزائدة
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} ', ': ', ', '], [';', '{', '{', '}', '}', ':', ','], $css);
        
        return trim($css);
    }

    /**
     * ضغط JS
     */
    protected function minifyJs(string $js): string
    {
        // إزالة التعليقات
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/\/\/.*$/', '', $js);
        
        // إزالة المسافات الزائدة
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }

    /**
     * تنظيف ملفات السجلات
     */
    protected function cleanupLogs(): void
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log');
        
        foreach ($files as $file) {
            $fileTime = File::lastModified($file);
            $daysDiff = (time() - $fileTime) / (60 * 60 * 24);
            
            // حذف الملفات الأقدم من 30 يوم
            if ($daysDiff > 30) {
                File::delete($file);
            }
        }
    }

    /**
     * تحسين الصور
     */
    protected function optimizeImages(): void
    {
        $this->info('🖼️ تحسين الصور...');
        
        // هذا مثال بسيط - في الإنتاج يمكن استخدام مكتبات متخصصة
        $this->line('  - فحص الصور في المجلد العام');
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $totalSize = 0;
        $imageCount = 0;
        
        foreach ($imageExtensions as $ext) {
            $images = File::glob(public_path("assets/images/*.{$ext}"));
            foreach ($images as $image) {
                $totalSize += File::size($image);
                $imageCount++;
            }
        }
        
        $this->line("  - تم العثور على {$imageCount} صورة بحجم إجمالي " . $this->formatBytes($totalSize));
    }

    /**
     * تنظيف الملفات المؤقتة
     */
    protected function cleanupTempFiles(): void
    {
        $this->info('🧹 تنظيف الملفات المؤقتة...');
        
        // تنظيف مجلد التخزين المؤقت
        $tempPaths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];
        
        foreach ($tempPaths as $path) {
            if (File::exists($path)) {
                $files = File::files($path);
                $deletedCount = 0;
                
                foreach ($files as $file) {
                    $fileTime = $file->getMTime();
                    $hoursDiff = (time() - $fileTime) / 3600;
                    
                    // حذف الملفات الأقدم من 24 ساعة
                    if ($hoursDiff > 24) {
                        File::delete($file->getPathname());
                        $deletedCount++;
                    }
                }
                
                if ($deletedCount > 0) {
                    $this->line("  - تم حذف {$deletedCount} ملف مؤقت من " . basename($path));
                }
            }
        }
    }

    /**
     * عرض إحصائيات الأداء
     */
    protected function displayPerformanceStats(): void
    {
        $this->newLine();
        $this->info('📊 إحصائيات الأداء:');
        
        // حجم الكاش
        $cacheSize = $this->getCacheSize();
        $this->line("  - حجم الكاش: {$cacheSize}");
        
        // استخدام الذاكرة
        $memoryUsage = $this->formatBytes(memory_get_peak_usage(true));
        $this->line("  - استخدام الذاكرة: {$memoryUsage}");
        
        // عدد الملفات المخزنة مؤقتاً
        $cachedFiles = $this->getCachedFilesCount();
        $this->line("  - الملفات المخزنة مؤقتاً: {$cachedFiles}");
    }

    /**
     * الحصول على حجم الكاش
     */
    protected function getCacheSize(): string
    {
        try {
            $cachePath = storage_path('framework/cache');
            $size = 0;
            
            if (File::exists($cachePath)) {
                $files = File::allFiles($cachePath);
                foreach ($files as $file) {
                    $size += $file->getSize();
                }
            }
            
            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'غير متاح';
        }
    }

    /**
     * عدد الملفات المخزنة مؤقتاً
     */
    protected function getCachedFilesCount(): int
    {
        try {
            $cachePath = storage_path('framework/cache');
            return File::exists($cachePath) ? count(File::allFiles($cachePath)) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * تنسيق البايتات
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
