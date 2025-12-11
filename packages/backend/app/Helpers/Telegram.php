<?php

namespace App\Helpers;

class Telegram
{
    public static function sendMessage(string $telegramId, string $message): void
    {
        $botToken = $_ENV['TELEGRAM_BOT_TOKEN']; // Asegúrate de agregar esto en tu archivo .env
        $apiUrl = "https://api.telegram.org/bot$botToken/sendMessage";

        $params = [
            'chat_id' => $telegramId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_exec($ch);
        curl_close($ch);
    }
}
