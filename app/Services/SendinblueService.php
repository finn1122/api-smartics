<?php

namespace App\Services;

use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SendinblueService
{
    private TransactionalEmailsApi $apiInstance;
    private array $defaultSender;

    public function __construct()
    {
        $this->initializeApiInstance();
        $this->setDefaultSender();
    }

    private function initializeApiInstance(): void
    {
        $apiKey = config('sendinblue.api_key');

        if (empty($apiKey)) {
            throw new InvalidArgumentException('Sendinblue API key is not configured');
        }

        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $apiKey);

        $this->apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );
    }

    private function setDefaultSender(): void
    {
        $this->defaultSender = [
            'name' => config('sendinblue.default_from_name'),
            'email' => config('sendinblue.default_from_email')
        ];

        if (empty($this->defaultSender['email'])) {
            throw new InvalidArgumentException('Sendinblue default sender email is not configured');
        }
    }

    public function sendEmail(
        array $to,
        string $subject,
        string $htmlContent,
        ?string $textContent = null,
        ?array $params = [],
        ?array $sender = null
    ): array {
        try {
            $this->validateRecipients($to);

            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail
                ->setTo($to)
                ->setSubject($subject)
                ->setHtmlContent($htmlContent)
                ->setSender($sender ?? $this->defaultSender);

            if ($textContent) {
                $sendSmtpEmail->setTextContent($textContent);
            }

            if (!empty($params)) {
                $sendSmtpEmail->setParams($params);
            }

            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);

            Log::info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject,
                'messageId' => $result->getMessageId()
            ]);

            return [
                'success' => true,
                'message_id' => $result->getMessageId(),
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Sendinblue error', [
                'message' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    private function validateRecipients(array $recipients): void
    {
        if (empty($recipients)) {
            throw new InvalidArgumentException('Recipients list cannot be empty');
        }

        foreach ($recipients as $recipient) {
            if (!isset($recipient['email']) || !filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid recipient email format');
            }
        }
    }

    public function getApiInstance(): TransactionalEmailsApi
    {
        return $this->apiInstance;
    }
}
