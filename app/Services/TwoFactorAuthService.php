<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * إنشاء مفتاح سري جديد للمصادقة الثنائية
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * إنشاء QR Code للمصادقة الثنائية
     */
    public function generateQrCode(User $user, string $secret): string
    {
        $companyName = config('app.name', 'متجر الأطفال');
        $companyEmail = $user->email;
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $secret
        );

        return QrCode::size(200)
            ->format('svg')
            ->generate($qrCodeUrl);
    }

    /**
     * تفعيل المصادقة الثنائية للمستخدم
     */
    public function enableTwoFactor(User $user, string $secret, string $code): bool
    {
        // التحقق من صحة الرمز
        if (!$this->verifyCode($secret, $code)) {
            return false;
        }

        // تشفير وحفظ المفتاح السري
        $user->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
            'two_factor_enabled_at' => now(),
        ]);

        // تسجيل في السجلات
        logger()->info('تم تفعيل المصادقة الثنائية', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
        ]);

        return true;
    }

    /**
     * إلغاء تفعيل المصادقة الثنائية
     */
    public function disableTwoFactor(User $user): bool
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled_at' => null,
        ]);

        // تسجيل في السجلات
        logger()->warning('تم إلغاء تفعيل المصادقة الثنائية', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip(),
        ]);

        return true;
    }

    /**
     * التحقق من رمز المصادقة الثنائية
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * التحقق من رمز المصادقة الثنائية للمستخدم
     */
    public function verifyUserCode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        try {
            $secret = Crypt::decryptString($user->two_factor_secret);
            return $this->verifyCode($secret, $code);
        } catch (\Exception $e) {
            logger()->error('خطأ في فك تشفير مفتاح المصادقة الثنائية', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * التحقق من رمز الاسترداد
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        
        if (in_array($code, $recoveryCodes)) {
            // إزالة الرمز المستخدم
            $recoveryCodes = array_diff($recoveryCodes, [$code]);
            $user->update(['two_factor_recovery_codes' => array_values($recoveryCodes)]);
            
            logger()->info('تم استخدام رمز استرداد المصادقة الثنائية', [
                'user_id' => $user->id,
                'ip' => request()->ip(),
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * إنشاء رموز الاسترداد
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }
        return $codes;
    }

    /**
     * إنشاء رموز استرداد جديدة
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $codes]);
        
        logger()->info('تم إنشاء رموز استرداد جديدة للمصادقة الثنائية', [
            'user_id' => $user->id,
        ]);
        
        return $codes;
    }

    /**
     * فحص ما إذا كان المستخدم لديه مصادقة ثنائية مفعلة
     */
    public function isEnabled(User $user): bool
    {
        return !empty($user->two_factor_secret);
    }
}
