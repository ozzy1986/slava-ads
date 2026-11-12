<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * Translates the stored quiz JSON into a human-readable line.
 * Unknown keys/values fall back to their raw string.
 */
final class QuizDecoder
{
    /** @var array<string, array<string, string>> */
    private const LABELS = [
        'goal' => [
            'leads' => 'Больше заявок',
            'cheaper' => 'Снизить цену заявки',
            'site' => 'Нужен сайт',
            'audit' => 'Аудит рекламы',
        ],
        'channel' => [
            'yandex' => 'Яндекс Директ',
            'google' => 'Google Ads',
            'both' => 'Везде',
            'unsure' => 'Нужен совет',
        ],
    ];

    public function decode(string $quizRaw): string
    {
        if ($quizRaw === '') {
            return '';
        }

        $data = json_decode($quizRaw, true);
        if (!is_array($data)) {
            return $quizRaw;
        }

        $parts = [];
        foreach ($data as $key => $value) {
            $value = (string) $value;
            $parts[] = self::LABELS[$key][$value] ?? $value;
        }

        return implode(' · ', $parts);
    }
}
