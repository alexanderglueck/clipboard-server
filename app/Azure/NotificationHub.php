<?php

namespace App\Azure;

use Exception;

class NotificationHub
{
    const API_VERSION = "api-version=2013-10";
    // const API_VERSION = "api-version=2015-04";

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

        $ch = curl_init($uri);

        if (in_array($notification->format, ["template", "apple", "gcm"])) {
            $contentType = "application/json";
        } else if ($notification->format == 'windows') {
            $contentType = "application/octet-stream";
        } else {
            $contentType = "application/xml";
        }

        $token = $this->generateSasToken($uri);

        $headers = [
            'Authorization: ' . $token,
            'Content-Type: ' . $contentType,
            'ServiceBusNotification-Format: ' . $notification->format,
        ];

        if ($tag != null) {
            $headers[] = "ServiceBusNotification-Tags: " . $tag;
        }

        # add headers for other platforms
        if (is_array($notification->headers)) {
            $headers = array_merge($headers, $notification->headers);
        }

        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $notification->payload
        ));

        // Send the request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === FALSE) {
            throw new Exception(curl_error($ch));
        }

        $info = curl_getinfo($ch);
        dump(["response" => $response]);

        dump(["info" => $info]);

        dump(["headers" => $headers, "payload" => $notification->payload]);
        if ($info['http_code'] <> 201) {
            throw new Exception('Error sending notification: ' . $info['http_code'] . ' msg: ' . $response);
        }


    }
}

