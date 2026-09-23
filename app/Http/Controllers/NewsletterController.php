<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Check if email already exists and is active
            $existingSubscriber = NewsletterSubscriber::where('email', $request->email)
                ->where('is_active', true)
                ->first();

            if ($existingSubscriber) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.newsletter_already_subscribed')
                ], 422);
            }

            $token = Str::random(32);

            NewsletterSubscriber::create([
                'email' => $request->email,
                'name' => $request->name,
                'is_active' => true,
                'token' => $token,
                'subscribed_at' => Carbon::now(),
            ]);

            // Here you could send a confirmation email if needed

            return response()->json([
                'success' => true,
                'message' => __('messages.newsletter_subscribed')
            ]);
        } catch (\Exception $exception) {
            Log::error('Newsletter subscription error: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.newsletter_error')
            ], 500);
        }
    }

    /**
     * Unsubscribe from newsletter
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|exists:newsletter_subscribers,token',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $subscriber = NewsletterSubscriber::where('token', $request->token)->first();

            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.error')
                ], 404);
            }

            $subscriber->update([
                'is_active' => false,
                'unsubscribed_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.success')
            ]);
        } catch (\Exception $exception) {
            Log::error('Newsletter unsubscribe error: ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.newsletter_error')
            ], 500);
        }
    }
}
