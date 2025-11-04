<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiSensyWhatsAppService
{
    protected $apiKey;
    protected $apiUrl;
    protected $campaignName;
    protected $templateId;

    public function __construct()
    {
        $this->apiKey = config('services.sensy.api_key');
        $this->apiUrl = config('services.sensy.api_url');
        $this->campaignName = config('services.sensy.campaign_name', 'Homework Notifications');
        $this->templateId = config('services.sensy.template_id');
    }

    /**
     * Send WhatsApp message to a student for homework notification
     *
     * @param string $phoneNumber Student phone number in +91XXXXXXXXXX format
     * @param string $studentName Student's name
     * @param string $homeworkTitle Homework title
     * @param string $homeworkLink Direct link to homework
     * @return array
     */
    public function sendHomeworkNotification($phoneNumber, $studentName, $homeworkTitle, $homeworkLink)
    {
        try {
            $payload = [
                'apiKey' => $this->apiKey,
                'campaignName' => $this->campaignName,
                'destination' => $phoneNumber,
                'userName' => $studentName,
                'templateParams' => [
                    $studentName,      // {{1}} - Student Name
                    $homeworkTitle,    // {{2}} - Homework Title
                    $homeworkLink,     // {{3}} - Homework Link
                ],
            ];

            // Add template ID if provided
            if (!empty($this->templateId)) {
                $payload['templateId'] = $this->templateId;
            }

            $response = Http::post($this->apiUrl . '/campaign/t1/api/v2', $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'phone' => $phoneNumber,
                    'student' => $studentName,
                    'response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'message' => 'WhatsApp notification sent successfully',
                    'data' => $responseData,
                ];
            } else {
                Log::error('WhatsApp message failed', [
                    'phone' => $phoneNumber,
                    'student' => $studentName,
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send WhatsApp notification',
                    'error' => $responseData ?? 'Unknown error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp service exception', [
                'phone' => $phoneNumber,
                'student' => $studentName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error sending WhatsApp notification: ' . $e->getMessage(),
            ];
        }
    }
}

