<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;

class TranslationService
{
    /**
     * Get translation for a specific key
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function get(string $key, array $replace = [], string $locale = null): string
    {
        $locale = $locale ?: App::getLocale();
        
        // Check if the translation is in cache
        $cacheKey = "translation_{$locale}_{$key}";
        
        if (Cache::has($cacheKey)) {
            $translation = Cache::get($cacheKey);
        } else {
            // Get the translation from the language file
            $translation = $this->getTranslationFromFile($key, $locale);
            
            // Cache the translation for 24 hours
            Cache::put($cacheKey, $translation, now()->addHours(24));
        }
        
        // Replace placeholders
        if (!empty($replace)) {
            foreach ($replace as $key => $value) {
                $translation = str_replace(":{$key}", $value, $translation);
            }
        }
        
        return $translation;
    }
    
    /**
     * Get all translations for a specific locale
     *
     * @param string|null $locale
     * @return array
     */
    public function getAll(string $locale = null): array
    {
        $locale = $locale ?: App::getLocale();
        
        // Check if all translations are in cache
        $cacheKey = "translations_{$locale}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Get all translations from language files
        $translations = $this->getAllTranslationsFromFiles($locale);
        
        // Cache all translations for 24 hours
        Cache::put($cacheKey, $translations, now()->addHours(24));
        
        return $translations;
    }
    
    /**
     * Get translation from language file
     *
     * @param string $key
     * @param string $locale
     * @return string
     */
    private function getTranslationFromFile(string $key, string $locale): string
    {
        // Split the key into file and key parts (e.g. 'messages.home' => ['messages', 'home'])
        $parts = explode('.', $key);
        
        if (count($parts) < 2) {
            return $key;
        }
        
        $file = $parts[0];
        $translationKey = $parts[1];
        
        // Get nested keys if any
        if (count($parts) > 2) {
            $translationKey = implode('.', array_slice($parts, 1));
        }
        
        // Get the translations from the file
        $translations = $this->getTranslationsFromFile($file, $locale);
        
        // Get the translation using dot notation for nested keys
        $translation = $this->getNestedValue($translations, $translationKey);
        
        return $translation ?: $key;
    }
    
    /**
     * Get all translations from language files
     *
     * @param string $locale
     * @return array
     */
    private function getAllTranslationsFromFiles(string $locale): array
    {
        $translations = [];
        
        // Get all PHP files in the language directory
        $langPath = lang_path($locale);
        
        if (!File::exists($langPath)) {
            return $translations;
        }
        
        $files = File::files($langPath);
        
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $translations[$filename] = include $file;
        }
        
        return $translations;
    }
    
    /**
     * Get translations from a specific file
     *
     * @param string $file
     * @param string $locale
     * @return array
     */
    private function getTranslationsFromFile(string $file, string $locale): array
    {
        $path = lang_path("{$locale}/{$file}.php");
        
        if (!File::exists($path)) {
            return [];
        }
        
        return include $path;
    }
    
    /**
     * Get nested value using dot notation
     *
     * @param array $array
     * @param string $key
     * @return string|null
     */
    private function getNestedValue(array $array, string $key)
    {
        // Split the key by dots
        $keys = explode('.', $key);
        
        // Traverse the array using the keys
        foreach ($keys as $nestedKey) {
            if (!isset($array[$nestedKey])) {
                return null;
            }
            
            $array = $array[$nestedKey];
        }
        
        return $array;
    }
    
    /**
     * Clear translation cache
     *
     * @param string|null $locale
     * @return void
     */
    public function clearCache(string $locale = null): void
    {
        if ($locale) {
            Cache::forget("translations_{$locale}");
        } else {
            // Clear cache for all locales
            $locales = ['en', 'ar']; // Add more locales as needed
            
            foreach ($locales as $loc) {
                Cache::forget("translations_{$loc}");
            }
        }
    }
    
    /**
     * Get the most used translations for a specific locale
     *
     * @param string|null $locale
     * @return array
     */
    public function getMostUsed(string $locale = null): array
    {
        $locale = $locale ?: App::getLocale();
        
        // Define the most used translation keys
        $mostUsedKeys = [
            'messages.home',
            'messages.shop',
            'messages.cart',
            'messages.about',
            'messages.contact',
            'messages.search',
            'messages.my_account',
            'messages.login',
            'messages.register',
            'messages.logout',
            'messages.add_to_cart',
            'messages.view_details',
            'messages.price',
            'messages.quantity',
            'messages.total',
            'messages.checkout',
        ];
        
        $translations = [];
        
        foreach ($mostUsedKeys as $key) {
            $translations[$key] = $this->get($key, [], $locale);
        }
        
        return $translations;
    }
}
