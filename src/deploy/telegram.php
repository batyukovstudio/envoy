<?php

declare(strict_types=1);

if (!function_exists('sendTelegramMessage')) {
    /**
     * Send a Telegram message through Bot API with optional proxy support.
     *
     * @return array<string, mixed>
     */
    function sendTelegramMessage(
        string $message,
        ?string $botToken = null,
        string|int|null $chatId = null,
        string|int|null $threadId = null,
        ?string $proxy = null,
        string $parseMode = 'HTML'
    ): array {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('cURL extension is required to send Telegram notifications.');
        }

        $botToken = $botToken ?: telegramFirstEnv(['TELEGRAM_BOT_ENVOY_TOKEN', 'TELEGRAM_BOT_TOKEN']);
        $chatId = $chatId ?? telegramFirstEnv(['TELEGRAM_CHAT_ID_FOR_ENVOY', 'TELEGRAM_CHAT_ID']);
        $threadId = $threadId ?? telegramFirstEnv(['TELEGRAM_THREAD_ID_FOR_ENVOY', 'TELEGRAM_THREAD_ID'], false);
        $proxy = $proxy ?: telegramFirstEnv(['TELEGRAM_PROXY', 'TELEGRAM_PROXY_FOR_ENVOY'], false);

        if (!$botToken) {
            throw new InvalidArgumentException('Missing TELEGRAM_BOT_TOKEN for Telegram notification.');
        }

        if ($chatId === null || $chatId === '') {
            throw new InvalidArgumentException('Missing TELEGRAM_CHAT_ID for Telegram notification.');
        }

        $endpoint = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $payload = [
            'chat_id' => (string)$chatId,
            'text' => $message,
            'parse_mode' => $parseMode,
        ];

        if ($threadId !== null && $threadId !== '') {
            $payload['message_thread_id'] = (string)$threadId;
        }

        $ch = curl_init($endpoint);
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL for Telegram notification.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload, '', '&', PHP_QUERY_RFC3986),
        ]);

        if ($proxy) {
            applyTelegramProxy($ch, $proxy);
        }

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $curlError = curl_error($ch);
            $curlErrNo = curl_errno($ch);
            curl_close($ch);
            throw new RuntimeException("Telegram request failed (cURL #{$curlErrNo}): {$curlError}");
        }

        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            $preview = telegramResponsePreview($responseBody);
            throw new RuntimeException("Telegram API returned HTTP {$httpCode}. Response: {$preview}");
        }

        if ($responseBody === '' || $responseBody === null) {
            throw new RuntimeException('Telegram API returned an empty response.');
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            $preview = telegramResponsePreview($responseBody);
            throw new RuntimeException("Telegram API returned invalid JSON. Response: {$preview}");
        }

        if (($decoded['ok'] ?? false) !== true) {
            $description = (string)($decoded['description'] ?? 'Unknown Telegram API error.');
            throw new RuntimeException("Telegram API error: {$description}");
        }

        return $decoded;
    }
}

if (!function_exists('notifyDeploySuccess')) {
    function notifyDeploySuccess(string $content): void
    {
        $config = telegramDeployConfig();
        $safeContent = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        sendTelegramMessage(
            message: "<b>Сервер обновлён</b> 👉👈\n\n{$safeContent}",
            botToken: $config['botToken'],
            chatId: $config['chatId'],
            threadId: $config['threadId'],
            proxy: $config['proxy'],
        );
    }
}

if (!function_exists('notifyDeployError')) {
    function notifyDeployError(string $task): void
    {
        $config = telegramDeployConfig();
        $safeTask = htmlspecialchars($task, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        sendTelegramMessage(
            message: "🔥<b>Ошибка обновления</b>🔥\n{$safeTask}",
            botToken: $config['botToken'],
            chatId: $config['chatId'],
            threadId: $config['threadId'],
            proxy: $config['proxy'],
        );
    }
}

if (!function_exists('telegramDeployConfig')) {
    /**
     * @return array{botToken:string, chatId:string, threadId:string|null, proxy:string|null}
     */
    function telegramDeployConfig(): array
    {
        return [
            'botToken' => (string)telegramFirstEnv(['TELEGRAM_BOT_ENVOY_TOKEN', 'TELEGRAM_BOT_TOKEN']),
            'chatId' => (string)telegramFirstEnv(['TELEGRAM_CHAT_ID_FOR_ENVOY', 'TELEGRAM_CHAT_ID']),
            'threadId' => telegramFirstEnv(['TELEGRAM_THREAD_ID_FOR_ENVOY', 'TELEGRAM_THREAD_ID'], false),
            'proxy' => telegramFirstEnv(['TELEGRAM_PROXY', 'TELEGRAM_PROXY_FOR_ENVOY'], false),
        ];
    }
}

if (!function_exists('telegramFirstEnv')) {
    /**
     * @param array<int, string> $keys
     */
    function telegramFirstEnv(array $keys, bool $required = true): ?string
    {
        foreach ($keys as $key) {
            $value = telegramRuntimeEnv($key, false);
            if ($value !== null) {
                return $value;
            }
        }

        if ($required) {
            throw new InvalidArgumentException(
                'Missing required environment variable. Expected one of: ' . implode(', ', $keys)
            );
        }

        return null;
    }
}

if (!function_exists('applyTelegramProxy')) {
    function applyTelegramProxy(\CurlHandle $ch, string $proxy): void
    {
        $parsed = parse_url($proxy);
        if (!is_array($parsed) || !isset($parsed['scheme'], $parsed['host'])) {
            throw new InvalidArgumentException("Invalid TELEGRAM_PROXY format: {$proxy}");
        }

        $scheme = strtolower($parsed['scheme']);
        $host = $parsed['host'];
        $port = $parsed['port'] ?? telegramDefaultProxyPort($scheme);

        if (!$port) {
            throw new InvalidArgumentException("Proxy port is missing for TELEGRAM_PROXY: {$proxy}");
        }

        $proxyType = match ($scheme) {
            'http' => CURLPROXY_HTTP,
            'https' => defined('CURLPROXY_HTTPS') ? CURLPROXY_HTTPS : CURLPROXY_HTTP,
            'socks5' => CURLPROXY_SOCKS5,
            'socks5h' => CURLPROXY_SOCKS5_HOSTNAME,
            default => throw new InvalidArgumentException(
                "Unsupported TELEGRAM_PROXY scheme '{$scheme}'. Supported: http, https, socks5, socks5h."
            ),
        };

        $proxyAddress = telegramFormatProxyHost($host) . ':' . $port;

        curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
        curl_setopt($ch, CURLOPT_PROXYTYPE, $proxyType);

        if (isset($parsed['user']) || isset($parsed['pass'])) {
            $user = rawurldecode((string)($parsed['user'] ?? ''));
            $pass = rawurldecode((string)($parsed['pass'] ?? ''));
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$user}:{$pass}");
        }
    }
}

if (!function_exists('telegramRuntimeEnv')) {
    function telegramRuntimeEnv(string $key, bool $required = true): ?string
    {
        $value = $_SERVER[$key] ?? $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            if ($required) {
                throw new InvalidArgumentException("Missing required environment variable: {$key}");
            }

            return null;
        }

        return (string)$value;
    }
}

if (!function_exists('telegramEnv')) {
    function telegramEnv(string $key, bool $required = true): ?string
    {
        return telegramRuntimeEnv($key, $required);
    }
}

if (!function_exists('telegramDefaultProxyPort')) {
    function telegramDefaultProxyPort(string $scheme): ?int
    {
        return match ($scheme) {
            'http' => 80,
            'https' => 443,
            'socks5', 'socks5h' => 1080,
            default => null,
        };
    }
}

if (!function_exists('telegramFormatProxyHost')) {
    function telegramFormatProxyHost(string $host): string
    {
        if (str_contains($host, ':') && !str_starts_with($host, '[')) {
            return "[{$host}]";
        }

        return $host;
    }
}

if (!function_exists('telegramResponsePreview')) {
    function telegramResponsePreview(string $responseBody, int $limit = 500): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $responseBody) ?? '');

        if ($normalized === '') {
            return '[empty]';
        }

        if (strlen($normalized) <= $limit) {
            return $normalized;
        }

        return substr($normalized, 0, $limit) . '...';
    }
}
