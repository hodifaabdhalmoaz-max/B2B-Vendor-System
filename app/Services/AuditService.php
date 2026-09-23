<?php

namespace App\Services;

use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Log a user activity.
     *
     * @param string $action
     * @param array|null $details
     * @param int|null $userId
     * @return \App\Models\UserActivity
     */
    public function log($action, $details = null, $userId = null)
    {
        $userId = $userId ?? (Auth::check() ? Auth::id() : null);
        
        $activity = null;

        try {
            $activity = UserActivity::create([
                'user_id' => $userId,
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'details' => $details,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::warning('AuditService::log failed — falling back to file log.', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
                'action'  => $action,
                'details' => $details,
            ]);
        }
        
        // Also log to the application log for critical actions
        $criticalActions = [
            'login_failed',
            'password_reset',
            'two_factor_failed',
            'admin_login',
            'payment_processed',
            'order_status_changed',
            'user_role_changed',
            'settings_changed',
        ];
        
        if (in_array($action, $criticalActions)) {
            Log::channel('audit')->info("Critical action: {$action}", [
                'user_id' => $userId,
                'ip' => request()->ip(),
                'details' => $details,
            ]);
        }
        
        return $activity;
    }
    
    /**
     * Log a model change.
     *
     * @param string $model
     * @param int $modelId
     * @param string $action
     * @param array $oldValues
     * @param array $newValues
     * @param int|null $userId
     * @return \App\Models\UserActivity
     */
    public function logModelChange($model, $modelId, $action, $oldValues, $newValues, $userId = null)
    {
        $userId = $userId ?? (Auth::check() ? Auth::id() : null);
        
        $details = [
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ];
        
        return $this->log($action, $details, $userId);
    }
    
    /**
     * Log a login attempt.
     *
     * @param string $email
     * @param bool $success
     * @param string|null $reason
     * @param int|null $userId
     * @return \App\Models\UserActivity
     */
    public function logLoginAttempt($email, $success, $reason = null, $userId = null)
    {
        $action = $success ? 'login_success' : 'login_failed';
        
        $details = [
            'email' => $email,
            'success' => $success,
        ];
        
        if ($reason) {
            $details['reason'] = $reason;
        }
        
        return $this->log($action, $details, $userId);
    }
    
    /**
     * Log a payment attempt.
     *
     * @param string $paymentMethod
     * @param float $amount
     * @param bool $success
     * @param string|null $transactionId
     * @param string|null $error
     * @param int|null $userId
     * @return \App\Models\UserActivity
     */
    public function logPaymentAttempt($paymentMethod, $amount, $success, $transactionId = null, $error = null, $userId = null)
    {
        $action = $success ? 'payment_success' : 'payment_failed';
        
        $details = [
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'success' => $success,
        ];
        
        if ($transactionId) {
            $details['transaction_id'] = $transactionId;
        }
        
        if ($error) {
            $details['error'] = $error;
        }
        
        return $this->log($action, $details, $userId);
    }
    
    /**
     * Log an order status change.
     *
     * @param int $orderId
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $comment
     * @param int|null $userId
     * @return \App\Models\UserActivity
     */
    public function logOrderStatusChange($orderId, $oldStatus, $newStatus, $comment = null, $userId = null)
    {
        $details = [
            'order_id' => $orderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ];
        
        if ($comment) {
            $details['comment'] = $comment;
        }
        
        return $this->log('order_status_changed', $details, $userId);
    }
    
    /**
     * Get user activities by user ID.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserActivities($userId, $limit = 20)
    {
        return UserActivity::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get activities by action.
     *
     * @param string $action
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivitiesByAction($action, $limit = 20)
    {
        return UserActivity::where('action', $action)
            ->latest()
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get recent activities.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentActivities($limit = 20)
    {
        return UserActivity::latest()
            ->limit($limit)
            ->get();
    }
}
