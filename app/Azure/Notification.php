<?php

namespace App\Azure;

use Exception;

class Notification
{
    public string $format;
    public string $payload;

    # array with keynames for headers
    # Note: Some headers are mandatory: Windows: X-WNS-Type, WindowsPhone: X-NotificationType
    # Note: For Apple you can set Expiry with header: ServiceBusNotification-ApnsExpiry in W3C DTF, YYYY-MM-DDThh:mmTZD (for example, 1997-07-16T19:20+01:00).
    public array $headers;

    /**
     * @throws Exception
     */
    public function __construct(string $format, string $payload)
    {
        if ( ! in_array($format, ["template", "apple", "windows", "gcm", "windowsphone"])) {
            throw new Exception('Invalid format: ' . $format);
        }

        $this->format = $format;
        $this->payload = $payload;
    }
}

