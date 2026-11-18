<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function send(string $phone, string $message): bool
    {
        $endpoint = config('attendance.whatsapp.endpoint');
        $token = config('attendance.whatsapp.token');

        if (! $endpoint) {
            Log::info('WhatsApp notification skipped because WHATSAPP_API_URL is not configured.', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return false;
        }

        try {
            $response = Http::withToken($token)
                ->asForm()
                ->timeout(15)
                ->post($endpoint, [
                    'target' => $phone,
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $exception) {
            Log::error('Failed to send WhatsApp notification.', [
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
