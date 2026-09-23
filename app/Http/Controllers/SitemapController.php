<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class SitemapController extends Controller
{
    /**
     * Generate XML sitemap
     */
    public function index(): Response
    {
        $sitemap = $this->generateSitemap();
        
        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Generate sitemap XML content
     */
    protected function generateSitemap(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // الصفحة الرئيسية
        $xml .= $this->addUrl(url('/'), '1.0', 'daily', now());

        // صفحات ثابتة
        $staticPages = [
            '/shop' => ['priority' => '0.9', 'changefreq' => 'daily'],
            '/about' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            '/contact' => ['priority' => '0.5', 'changefreq' => 'monthly'],
            '/privacy' => ['priority' => '0.3', 'changefreq' => 'yearly'],
            '/terms' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        foreach ($staticPages as $page => $config) {
            $xml .= $this->addUrl(
                url($page),
                $config['priority'],
                $config['changefreq'],
                now()
            );
        }

        // الفئات
        try {
            $categories = Category::where('status', 1)->get();
            foreach ($categories as $category) {
                $xml .= $this->addUrl(
                    route('shop.category', $category->slug),
                    '0.8',
                    'weekly',
                    $category->updated_at
                );
            }
        } catch (\Exception $e) {
            // تجاهل الأخطاء في حالة عدم وجود الجدول
        }

        // العلامات التجارية
        try {
            $brands = Brand::where('status', 1)->get();
            foreach ($brands as $brand) {
                $xml .= $this->addUrl(
                    route('shop.brand', $brand->slug),
                    '0.7',
                    'weekly',
                    $brand->updated_at
                );
            }
        } catch (\Exception $e) {
            // تجاهل الأخطاء في حالة عدم وجود الجدول
        }

        // المنتجات
        try {
            $products = Product::where('status', 1)
                ->select('id', 'slug', 'updated_at')
                ->orderBy('updated_at', 'desc')
                ->limit(1000) // تحديد عدد المنتجات لتجنب الملفات الكبيرة
                ->get();

            foreach ($products as $product) {
                $xml .= $this->addUrl(
                    route('shop.product.details', $product->slug),
                    '0.6',
                    'weekly',
                    $product->updated_at
                );
            }
        } catch (\Exception $e) {
            // تجاهل الأخطاء في حالة عدم وجود الجدول
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Add URL to sitemap
     */
    protected function addUrl(string $url, string $priority, string $changefreq, $lastmod = null): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url, ENT_XML1, 'UTF-8') . "</loc>\n";
        
        if ($lastmod) {
            $xml .= "    <lastmod>" . $lastmod->format('Y-m-d\TH:i:s+00:00') . "</lastmod>\n";
        }
        
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }

    /**
     * Generate robots.txt content
     */
    public function robots(): Response
    {
        $content = view('robots')->render();
        
        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
