<?php

declare(strict_types=1);

namespace SlavaAds\Notification;

use SlavaAds\Lead\Lead;
use SlavaAds\Lead\QuizDecoder;

/**
 * Single source of truth for lead notification content (DRY): builds the
 * ordered label/value pairs shared by every channel. Channels decide only
 * how to render them.
 */
final class LeadMessageFormatter
{
    public function __construct(private readonly QuizDecoder $quizDecoder)
    {
    }

    public function title(Lead $lead): string
    {
        return 'Новая заявка №' . ($lead->id ?? 0);
    }

    /**
     * Ordered label => value pairs, omitting empty optional fields.
     *
     * @return array<string, string>
     */
    public function fields(Lead $lead): array
    {
        $fields = [
            'Имя' => $lead->name,
            'Контакт' => $lead->phone,
        ];

        $quiz = $this->quizDecoder->decode($lead->quizRaw);
        if ($lead->message !== '') {
            $fields['Задача'] = $lead->message;
        }
        if ($quiz !== '') {
            $fields['Квиз'] = $quiz;
        }
        if ($lead->source !== '') {
            $fields['Блок'] = $lead->source;
        }
        if ($lead->page !== '') {
            $fields['Страница'] = $lead->page;
        }
        if (!$lead->utm->isEmpty()) {
            $fields['UTM'] = $lead->utm->toLine();
        }
        if ($lead->ip !== '') {
            $fields['IP'] = $lead->ip;
        }

        return $fields;
    }
}
