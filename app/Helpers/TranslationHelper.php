<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class TranslationHelper
{
    /**
     * Get translation with fallback to English
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public static function trans($key, $replace = [], $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        
        // Try to get translation in current locale
        $translation = __($key, $replace, $locale);
        
        // If translation not found and current locale is not English, try English
        if ($translation === $key && $locale !== 'en') {
            $translation = __($key, $replace, 'en');
        }
        
        return $translation;
    }

    /**
     * Get all available locales
     *
     * @return array
     */
    public static function getAvailableLocales()
    {
        $locales = [];
        $langPath = resource_path('lang');
        
        if (File::exists($langPath)) {
            $directories = File::directories($langPath);
            foreach ($directories as $directory) {
                $locale = basename($directory);
                $locales[$locale] = self::getLocaleDisplayName($locale);
            }
        }
        
        return $locales;
    }

    /**
     * Get display name for locale
     *
     * @param string $locale
     * @return string
     */
    public static function getLocaleDisplayName($locale)
    {
        $names = [
            'ar' => 'العربية',
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
        ];

        return $names[$locale] ?? $locale;
    }

    /**
     * Check if locale is RTL
     *
     * @param string|null $locale
     * @return bool
     */
    public static function isRtl($locale = null)
    {
        $locale = $locale ?: App::getLocale();
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        
        return in_array($locale, $rtlLocales);
    }

    /**
     * Get direction attribute for HTML
     *
     * @param string|null $locale
     * @return string
     */
    public static function getDirection($locale = null)
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    /**
     * Get text alignment class
     *
     * @param string|null $locale
     * @return string
     */
    public static function getTextAlign($locale = null)
    {
        return self::isRtl($locale) ? 'text-right' : 'text-left';
    }

    /**
     * Get float direction class
     *
     * @param string|null $locale
     * @return string
     */
    public static function getFloatDirection($locale = null)
    {
        return self::isRtl($locale) ? 'float-right' : 'float-left';
    }

    /**
     * Get margin/padding direction classes
     *
     * @param string|null $locale
     * @return array
     */
    public static function getDirectionClasses($locale = null)
    {
        $isRtl = self::isRtl($locale);
        
        return [
            'text_align' => $isRtl ? 'text-right' : 'text-left',
            'float_start' => $isRtl ? 'float-right' : 'float-left',
            'float_end' => $isRtl ? 'float-left' : 'float-right',
            'margin_start' => $isRtl ? 'mr' : 'ml',
            'margin_end' => $isRtl ? 'ml' : 'mr',
            'padding_start' => $isRtl ? 'pr' : 'pl',
            'padding_end' => $isRtl ? 'pl' : 'pr',
        ];
    }

    /**
     * Format number according to locale
     *
     * @param float $number
     * @param int $decimals
     * @param string|null $locale
     * @return string
     */
    public static function formatNumber($number, $decimals = 2, $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        
        if ($locale === 'ar') {
            // Arabic number formatting
            $formatted = number_format($number, $decimals, '.', ',');
            // Convert to Arabic-Indic numerals if needed
            return self::convertToArabicNumerals($formatted);
        }
        
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Convert numbers to Arabic-Indic numerals
     *
     * @param string $text
     * @return string
     */
    public static function convertToArabicNumerals($text)
    {
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        
        return str_replace($western, $arabic, $text);
    }

    /**
     * Format currency according to locale
     *
     * @param float $amount
     * @param string $currency
     * @param string|null $locale
     * @return string
     */
    public static function formatCurrency($amount, $currency = 'SAR', $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        $formattedAmount = self::formatNumber($amount, 2, $locale);
        
        if ($locale === 'ar') {
            return $formattedAmount . ' ' . self::getCurrencySymbol($currency, $locale);
        }
        
        return self::getCurrencySymbol($currency, $locale) . ' ' . $formattedAmount;
    }

    /**
     * Get currency symbol
     *
     * @param string $currency
     * @param string|null $locale
     * @return string
     */
    public static function getCurrencySymbol($currency, $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        
        $symbols = [
            'SAR' => $locale === 'ar' ? 'ر.س' : 'SAR',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'AED' => $locale === 'ar' ? 'د.إ' : 'AED',
            'KWD' => $locale === 'ar' ? 'د.ك' : 'KWD',
            'QAR' => $locale === 'ar' ? 'ر.ق' : 'QAR',
            'BHD' => $locale === 'ar' ? 'د.ب' : 'BHD',
            'OMR' => $locale === 'ar' ? 'ر.ع' : 'OMR',
            'JOD' => $locale === 'ar' ? 'د.أ' : 'JOD',
            'EGP' => $locale === 'ar' ? 'ج.م' : 'EGP',
        ];

        return $symbols[$currency] ?? $currency;
    }

    /**
     * Format date according to locale
     *
     * @param string|\DateTime $date
     * @param string $format
     * @param string|null $locale
     * @return string
     */
    public static function formatDate($date, $format = null, $locale = null)
    {
        $locale = $locale ?: App::getLocale();
        
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        if ($locale === 'ar') {
            $format = $format ?: 'j F Y';
            $formatted = $date->format($format);
            
            // Convert month names to Arabic
            $months = [
                'January' => 'يناير',
                'February' => 'فبراير',
                'March' => 'مارس',
                'April' => 'أبريل',
                'May' => 'مايو',
                'June' => 'يونيو',
                'July' => 'يوليو',
                'August' => 'أغسطس',
                'September' => 'سبتمبر',
                'October' => 'أكتوبر',
                'November' => 'نوفمبر',
                'December' => 'ديسمبر',
            ];
            
            foreach ($months as $english => $arabic) {
                $formatted = str_replace($english, $arabic, $formatted);
            }
            
            return self::convertToArabicNumerals($formatted);
        }
        
        $format = $format ?: 'F j, Y';
        return $date->format($format);
    }

    /**
     * Get common UI translations
     *
     * @param string|null $locale
     * @return array
     */
    public static function getCommonTranslations($locale = null)
    {
        $locale = $locale ?: App::getLocale();
        
        return [
            'add_to_cart' => self::trans('messages.add_to_cart', [], $locale),
            'view_details' => self::trans('messages.view_details', [], $locale),
            'quick_view' => self::trans('messages.quick_view', [], $locale),
            'add_to_wishlist' => self::trans('messages.add_to_wishlist', [], $locale),
            'compare' => self::trans('messages.compare', [], $locale),
            'share' => self::trans('messages.share', [], $locale),
            'in_stock' => self::trans('messages.in_stock', [], $locale),
            'out_of_stock' => self::trans('messages.out_of_stock', [], $locale),
            'sale' => self::trans('messages.sale', [], $locale),
            'new' => self::trans('messages.new', [], $locale),
            'featured' => self::trans('messages.featured', [], $locale),
            'loading' => self::trans('messages.loading', [], $locale),
            'search' => self::trans('messages.search', [], $locale),
            'filter' => self::trans('messages.filter', [], $locale),
            'sort_by' => self::trans('messages.sort_by', [], $locale),
            'show' => self::trans('messages.show', [], $locale),
            'price' => self::trans('messages.price', [], $locale),
            'quantity' => self::trans('messages.quantity', [], $locale),
            'total' => self::trans('messages.total', [], $locale),
            'subtotal' => self::trans('messages.subtotal', [], $locale),
            'checkout' => self::trans('messages.checkout', [], $locale),
            'continue_shopping' => self::trans('messages.continue_shopping', [], $locale),
        ];
    }
}
