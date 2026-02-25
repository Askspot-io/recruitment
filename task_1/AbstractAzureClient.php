<?php

declare(strict_types=1);

abstract readonly class AbstractAzureClient
{
    private const int MAX_RETRIES = 9;
    private const int RETRY_DELAY = 2;

    /**
     * @return array{0: string, 1: int}
     */
    protected function executeRequest(
        string $method,
        string $url,
        ?string $body,
        array $headers,
        ?int $timeoutSeconds,
        string $action,
    ): array {
        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            $curl = curl_init($url);
            $options = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
            ];

            if (null !== $timeoutSeconds) {
                $options[CURLOPT_TIMEOUT] = $timeoutSeconds;
            }

            if ($method === 'POST') {
                $options[CURLOPT_POST] = true;
            } else {
                $options[CURLOPT_CUSTOMREQUEST] = $method;
            }

            if (null !== $body) {
                $options[CURLOPT_POSTFIELDS] = $body;
            }

            curl_setopt_array($curl, $options);

            $responseBody = curl_exec($curl);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if (false === $responseBody) {
                $error = sprintf('cURL error when %s (%s %s): %s', $action, $method, $url, curl_error($curl));

                if ($attempt < self::MAX_RETRIES) {
                    sleep(self::RETRY_DELAY);
                    continue;
                }

                throw new RuntimeException($error);
            }

            if ($responseCode >= 400) {
                throw new RuntimeException(sprintf(
                    'HTTP error when %s (%s %s): HTTP %d - %s',
                    $action,
                    $method,
                    $url,
                    $responseCode,
                    $responseBody
                ));
            }

            return [$responseBody, $responseCode];
        }

        throw new RuntimeException(sprintf('Unable to %s after %d attempts.', $action, self::MAX_RETRIES + 1));
    }
}
