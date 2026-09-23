<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SitemapService
{
    /**
     * Generate XML sitemap
     *
     * @return string Path to generated sitemap
     */
    public function generateSitemap(): string
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        
        // Add static pages
        $staticPages = ['/', '/shop', '/contact', '/about', '/cart', '/checkout'];
        foreach ($staticPages as $page) {
            $sitemap .= $this->createUrlEntry(URL::to($page), '1.0', 'daily');
        }
        
        // Add products
        $products = Product::where('status', 'active')->get();
        foreach ($products as $product) {
            $sitemap .= $this->createUrlEntry(
                URL::to('/product/' . $product->slug),
                '0.8',
                'weekly',
                $product->updated_at->toIso8601String()
            );
        }
        
        // Add categories
        $categories = Category::all();
        foreach ($categories as $category) {
            $sitemap .= $this->createUrlEntry(
                URL::to('/category/' . $category->slug),
                '0.8',
                'weekly'
            );
        }
        
        $sitemap .= '</urlset>';
        
        // Save sitemap
        Storage::disk('public')->put('sitemap.xml', $sitemap);
        
        return 'sitemap.xml';
    }
    
    /**
     * Create URL entry for sitemap
     */
    private function createUrlEntry(string $url, string $priority, string $changefreq, string $lastmod = null): string
    {
        $entry = '  <url>' . PHP_EOL;
        $entry .= '    <loc>' . $url . '</loc>' . PHP_EOL;
        
        if ($lastmod) {
            $entry .= '    <lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
        }
        
        $entry .= '    <changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
        $entry .= '    <priority>' . $priority . '</priority>' . PHP_EOL;
        $entry .= '  </url>' . PHP_EOL;
        
        return $entry;
    }
}