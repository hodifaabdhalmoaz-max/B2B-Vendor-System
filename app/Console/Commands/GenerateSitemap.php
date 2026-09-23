<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.xml file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');
        
        // Create sitemap content
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        
        // Add static pages
        $staticPages = [
            route('home.index'),
            route('shop.index'),
            route('about'),
            route('contact'),
            route('faq'),
        ];
        
        foreach ($staticPages as $url) {
            $sitemap .= $this->createUrlEntry($url);
        }
        
        // Add product pages
        $products = Product::where('stock_status', 'instock')->get();
        foreach ($products as $product) {
            $url = route('shop.product.details', $product->slug);
            $lastmod = $product->updated_at->format('Y-m-d');
            $sitemap .= $this->createUrlEntry($url, $lastmod, 'weekly', '0.8');
        }
        
        // Add category pages
        $categories = Category::all();
        foreach ($categories as $category) {
            $url = route('shop.category', $category->slug);
            $lastmod = $category->updated_at->format('Y-m-d');
            $sitemap .= $this->createUrlEntry($url, $lastmod, 'weekly', '0.7');
        }
        
        // Add brand pages
        $brands = Brand::all();
        foreach ($brands as $brand) {
            $url = route('shop.brand', $brand->slug);
            $lastmod = $brand->updated_at->format('Y-m-d');
            $sitemap .= $this->createUrlEntry($url, $lastmod, 'weekly', '0.7');
        }
        
        // Close sitemap
        $sitemap .= '</urlset>';
        
        // Save sitemap
        File::put(public_path('sitemap.xml'), $sitemap);
        
        $this->info('Sitemap generated successfully!');
    }
    
    /**
     * Create a URL entry for the sitemap.
     *
     * @param string $url
     * @param string|null $lastmod
     * @param string $changefreq
     * @param string $priority
     * @return string
     */
    protected function createUrlEntry($url, $lastmod = null, $changefreq = 'monthly', $priority = '0.5')
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
