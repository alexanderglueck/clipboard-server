<?php

namespace App\Azure;

use Exception;
use Illuminate\Support\Facades\Http;

class NotificationHub
{
//    const API_VERSION = "api-version=2013-10";
    const API_VERSION = "api-version=2015-04";

    private string $endpoint;
    private string $hubPath;
    private string $sasKeyName;
    private string $sasKeyValue;

    public function __construct($connectionString, $hubPath)
    {
        $this->hubPath = $hubPath;

        $this->parseConnectionString($connectionString);
    }

    /**
     * @throws Exception
     */
    private function parseConnectionString(string $connectionString): void
    {
        $parts = explode(";", $connectionString);
        if (sizeof($parts) != 3) {
            throw new Exception("Error parsing connection string: " . $connectionString);
        }

        foreach ($parts as $part) {
            if (str_starts_with($part, "Endpoint")) {
                $this->endpoint = "https" . substr($part, 11);
            } else if (str_starts_with($part, "SharedAccessKeyName")) {
                $this->sasKeyName = substr($part, 20);
            } else if (str_starts_with($part, "SharedAccessKey")) {
                $this->sasKeyValue = substr($part, 16);
            }
        }
    }

    private function generateSasToken(string $uri): string
    {
        $targetUri = strtolower(rawurlencode(strtolower($uri)));

        $expires = time();
        $expiresInMins = 60;
        $expires = $expires + $expiresInMins * 60;
        $toSign = $targetUri . "\n" . $expires;

        $signature = rawurlencode(base64_encode(hash_hmac('sha256', $toSign, $this->sasKeyValue, TRUE)));

        return "SharedAccessSignature sr=" . $targetUri . "&sig="
            . $signature . "&se=" . $expires . "&skn=" . $this->sasKeyName;
    }


    public function sendNotification(Notification $notification, ?string $tag = null): void
    {
        # build uri
        $uri = $this->endpoint . $this->hubPath . "/messages?" . NotificationHub::API_VERSION;

        if (in_array($notification->format, ["template", "apple", "gcm"])) {
            $contentType = "application/json";
        } else if ($notification->format == 'windows') {
            $contentType = "application/octet-stream";
        } else {
            $contentType = "application/xml";
        }

        $token = $this->generateSasToken($uri);

        $headers = [
            'Authorization' => $token,
            'ServiceBusNotification-Format' => $notification->format,
        ];

        if ($tag != null) {
            $headers["ServiceBusNotification-Tags"] = $tag;
        }

        # add headers for other platforms
        if (is_array($notification->headers)) {
            foreach ($notification->headers as $key => $value) {
                $headers[$key] = $value;
            }
        }

        $res = Http::withHeaders($headers)->withBody($notification->payload, $contentType)->post($uri);

        if ($res->status() <> 201) {
            throw new Exception('Error sending notification: ' . $res->status() . ' msg: ' . $res->body());
        }
    }
}

