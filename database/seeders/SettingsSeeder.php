<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General Settings
        $this->createSetting('site_name', 'اسم الموقع', 'متجر الأطفال', 'text', 'general', 1);
        $this->createSetting('site_title', 'عنوان الموقع', 'متجر الأطفال - متجر إلكتروني متخصص في منتجات الأطفال', 'text', 'general', 2);
        $this->createSetting('site_description', 'وصف الموقع', 'متجر إلكتروني متخصص في منتجات الأطفال يقدم مجموعة واسعة من الملابس والألعاب والإكسسوارات للأطفال بجودة عالية وأسعار مناسبة', 'textarea', 'general', 3);
        $this->createSetting('site_keywords', 'الكلمات المفتاحية للموقع', 'متجر أطفال، ملابس أطفال، ألعاب أطفال، إكسسوارات أطفال، منتجات أطفال، هدايا أطفال، مستلزمات أطفال', 'textarea', 'general', 4);
        $this->createSetting('site_logo', 'شعار الموقع', '', 'file', 'general', 5);
        $this->createSetting('site_favicon', 'أيقونة الموقع', '', 'file', 'general', 6);
        $this->createSetting('site_email', 'البريد الإلكتروني للموقع', 'info@example.com', 'email', 'general', 7);
        $this->createSetting('site_phone', 'رقم هاتف الموقع', '+1234567890', 'text', 'general', 8);
        $this->createSetting('site_address', 'عنوان الموقع', 'شارع الرياض، المملكة العربية السعودية', 'textarea', 'general', 9);
        $this->createSetting('site_currency', 'عملة الموقع', 'ريال', 'text', 'general', 10);
        $this->createSetting('site_currency_symbol', 'رمز العملة', 'ر.س', 'text', 'general', 11);
        $this->createSetting('site_tax_percentage', 'نسبة الضريبة', '15', 'number', 'general', 12);
        $this->createSetting('site_timezone', 'المنطقة الزمنية', 'Asia/Riyadh', 'text', 'general', 13);
        $this->createSetting('site_language', 'لغة الموقع', 'ar', 'select', 'general', 14, json_encode(['ar' => 'العربية', 'en' => 'English']));
        $this->createSetting('site_maintenance_mode', 'وضع الصيانة', '0', 'checkbox', 'general', 15);
        $this->createSetting('site_maintenance_message', 'رسالة الصيانة', 'الموقع قيد الصيانة حالياً، يرجى المحاولة لاحقاً.', 'textarea', 'general', 16);
        
        // Store Settings
        $this->createSetting('store_name', 'اسم المتجر', 'متجر الأطفال', 'text', 'store', 1);
        $this->createSetting('store_owner', 'مالك المتجر', 'محمد أحمد', 'text', 'store', 2);
        $this->createSetting('store_address', 'عنوان المتجر', 'شارع الرياض، المملكة العربية السعودية', 'textarea', 'store', 3);
        $this->createSetting('store_email', 'البريد الإلكتروني للمتجر', 'store@example.com', 'email', 'store', 4);
        $this->createSetting('store_phone', 'رقم هاتف المتجر', '+1234567890', 'text', 'store', 5);
        $this->createSetting('store_working_hours', 'ساعات العمل', 'من الأحد إلى الخميس: 9:00 صباحاً - 9:00 مساءً\nالجمعة والسبت: 2:00 مساءً - 10:00 مساءً', 'textarea', 'store', 6);
        $this->createSetting('store_country', 'دولة المتجر', 'المملكة العربية السعودية', 'text', 'store', 7);
        $this->createSetting('store_city', 'مدينة المتجر', 'الرياض', 'text', 'store', 8);
        $this->createSetting('store_zip', 'الرمز البريدي للمتجر', '12345', 'text', 'store', 9);
        $this->createSetting('store_tax_number', 'الرقم الضريبي للمتجر', '123456789', 'text', 'store', 10);
        $this->createSetting('store_registration_number', 'رقم السجل التجاري', '987654321', 'text', 'store', 11);
        $this->createSetting('store_logo', 'شعار المتجر', '', 'file', 'store', 12);
        $this->createSetting('store_favicon', 'أيقونة المتجر', '', 'file', 'store', 13);
        
        // Payment Settings
        $this->createSetting('payment_cod_enabled', 'تفعيل الدفع عند الاستلام', '1', 'checkbox', 'payment', 1);
        $this->createSetting('payment_cod_title', 'عنوان الدفع عند الاستلام', 'الدفع عند الاستلام', 'text', 'payment', 2);
        $this->createSetting('payment_cod_description', 'وصف الدفع عند الاستلام', 'ادفع نقداً عند استلام طلبك.', 'textarea', 'payment', 3);
        $this->createSetting('payment_bank_enabled', 'تفعيل التحويل البنكي', '1', 'checkbox', 'payment', 4);
        $this->createSetting('payment_bank_title', 'عنوان التحويل البنكي', 'تحويل بنكي', 'text', 'payment', 5);
        $this->createSetting('payment_bank_description', 'وصف التحويل البنكي', 'قم بالتحويل البنكي إلى حسابنا وأرسل لنا صورة الإيصال.', 'textarea', 'payment', 6);
        $this->createSetting('payment_bank_account_name', 'اسم صاحب الحساب البنكي', 'متجر الأطفال', 'text', 'payment', 7);
        $this->createSetting('payment_bank_account_number', 'رقم الحساب البنكي', '1234567890', 'text', 'payment', 8);
        $this->createSetting('payment_bank_name', 'اسم البنك', 'البنك الأهلي', 'text', 'payment', 9);
        $this->createSetting('payment_bank_code', 'رمز البنك', 'NCB', 'text', 'payment', 10);
        $this->createSetting('payment_bank_branch', 'فرع البنك', 'الرياض', 'text', 'payment', 11);
        $this->createSetting('payment_whatsapp_enabled', 'تفعيل الدفع عبر واتساب', '1', 'checkbox', 'payment', 12);
        $this->createSetting('payment_whatsapp_title', 'عنوان الدفع عبر واتساب', 'الدفع عبر واتساب', 'text', 'payment', 13);
        $this->createSetting('payment_whatsapp_description', 'وصف الدفع عبر واتساب', 'أكد طلبك عبر واتساب وسنتواصل معك لإتمام عملية الدفع.', 'textarea', 'payment', 14);
        $this->createSetting('payment_whatsapp_number', 'رقم واتساب للدفع', '+1234567890', 'text', 'payment', 15);
        $this->createSetting('payment_whatsapp_message', 'رسالة واتساب للدفع', 'مرحبا، أرغب في تأكيد طلبي رقم {order_id} بقيمة {order_total}.', 'textarea', 'payment', 16);
        
        // Shipping Settings
        $this->createSetting('shipping_enabled', 'تفعيل الشحن', '1', 'checkbox', 'shipping', 1);
        $this->createSetting('shipping_method', 'طريقة الشحن', 'flat_rate', 'select', 'shipping', 2, json_encode(['flat_rate' => 'سعر ثابت', 'free_shipping' => 'شحن مجاني', 'local_pickup' => 'استلام محلي']));
        $this->createSetting('shipping_flat_rate', 'سعر الشحن الثابت', '20', 'number', 'shipping', 3);
        $this->createSetting('shipping_free_threshold', 'الحد الأدنى للشحن المجاني', '200', 'number', 'shipping', 4);
        $this->createSetting('shipping_local_pickup_cost', 'تكلفة الاستلام المحلي', '0', 'number', 'shipping', 5);
        $this->createSetting('shipping_countries', 'دول الشحن', 'المملكة العربية السعودية', 'textarea', 'shipping', 6);
        $this->createSetting('shipping_states', 'مناطق الشحن', 'الرياض، جدة، الدمام، مكة، المدينة', 'textarea', 'shipping', 7);
        $this->createSetting('shipping_cities', 'مدن الشحن', 'الرياض، جدة، الدمام، مكة، المدينة', 'textarea', 'shipping', 8);
        $this->createSetting('shipping_estimated_days', 'أيام الشحن المتوقعة', '3-5', 'text', 'shipping', 9);
        $this->createSetting('shipping_tracking_enabled', 'تفعيل تتبع الشحن', '1', 'checkbox', 'shipping', 10);
        
        // Email Settings
        $this->createSetting('email_from_name', 'اسم المرسل', 'متجر الأطفال', 'text', 'email', 1);
        $this->createSetting('email_from_address', 'عنوان المرسل', 'info@example.com', 'email', 'email', 2);
        $this->createSetting('email_host', 'خادم البريد الإلكتروني', 'smtp.example.com', 'text', 'email', 3);
        $this->createSetting('email_port', 'منفذ البريد الإلكتروني', '587', 'number', 'email', 4);
        $this->createSetting('email_username', 'اسم مستخدم البريد الإلكتروني', 'info@example.com', 'text', 'email', 5);
        $this->createSetting('email_password', 'كلمة مرور البريد الإلكتروني', 'password', 'password', 'email', 6);
        $this->createSetting('email_encryption', 'تشفير البريد الإلكتروني', 'tls', 'select', 'email', 7, json_encode(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None']));
        $this->createSetting('email_order_notification', 'إشعار الطلب عبر البريد الإلكتروني', '1', 'checkbox', 'email', 8);
        $this->createSetting('email_order_notification_to', 'إرسال إشعار الطلب إلى', 'orders@example.com', 'email', 'email', 9);
        $this->createSetting('email_order_template', 'قالب بريد الطلب', 'قالب بريد الطلب الافتراضي', 'textarea', 'email', 10);
        
        // Social Settings
        $this->createSetting('social_facebook', 'فيسبوك', 'https://facebook.com/example', 'text', 'social', 1);
        $this->createSetting('social_twitter', 'تويتر', 'https://twitter.com/example', 'text', 'social', 2);
        $this->createSetting('social_instagram', 'انستغرام', 'https://instagram.com/example', 'text', 'social', 3);
        $this->createSetting('social_youtube', 'يوتيوب', 'https://youtube.com/example', 'text', 'social', 4);
        $this->createSetting('social_whatsapp', 'واتساب', '+1234567890', 'text', 'social', 5);
        $this->createSetting('social_tiktok', 'تيك توك', 'https://tiktok.com/@example', 'text', 'social', 6);
        $this->createSetting('social_snapchat', 'سناب شات', 'https://snapchat.com/add/example', 'text', 'social', 7);
        $this->createSetting('social_linkedin', 'لينكد إن', 'https://linkedin.com/company/example', 'text', 'social', 8);
        
        // Analytics Settings
        $this->createSetting('analytics_google_enabled', 'تفعيل تحليلات جوجل', '0', 'checkbox', 'analytics', 1);
        $this->createSetting('analytics_google_id', 'معرف تحليلات جوجل', 'UA-XXXXXXXXX-X', 'text', 'analytics', 2);
        $this->createSetting('analytics_facebook_pixel_enabled', 'تفعيل بيكسل فيسبوك', '0', 'checkbox', 'analytics', 3);
        $this->createSetting('analytics_facebook_pixel_id', 'معرف بيكسل فيسبوك', 'XXXXXXXXXXXXXXXXXX', 'text', 'analytics', 4);
        $this->createSetting('analytics_snapchat_pixel_enabled', 'تفعيل بيكسل سناب شات', '0', 'checkbox', 'analytics', 5);
        $this->createSetting('analytics_snapchat_pixel_id', 'معرف بيكسل سناب شات', 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', 'text', 'analytics', 6);
        $this->createSetting('analytics_tiktok_pixel_enabled', 'تفعيل بيكسل تيك توك', '0', 'checkbox', 'analytics', 7);
        $this->createSetting('analytics_tiktok_pixel_id', 'معرف بيكسل تيك توك', 'XXXXXXXXXXXXXXXXXX', 'text', 'analytics', 8);
    }
    
    /**
     * Create a setting.
     */
    private function createSetting($key, $displayName, $value, $type, $group, $order, $options = null)
    {
        Setting::create([
            'key' => $key,
            'display_name' => $displayName,
            'value' => $value,
            'type' => $type,
            'group' => $group,
            'order' => $order,
            'options' => $options,
        ]);
    }
}
