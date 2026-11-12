<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use SlavaAds\Http\Request;

/**
 * Builds a sanitized Lead from a Request. Field length caps live here,
 * matching the database column sizes.
 */
final class LeadFactory
{
    public function fromRequest(Request $request): Lead
    {
        return new Lead(
            name: $request->input('name', 120),
            phone: $request->input('phone', 120),
            message: $request->input('message', 2000),
            quizRaw: $request->input('quiz', 255),
            source: $request->input('source', 80),
            page: $request->input('page', 255),
            referrer: $request->input('referrer', 255),
            utm: new Utm(
                source: $request->input('utm_source', 120),
                medium: $request->input('utm_medium', 120),
                campaign: $request->input('utm_campaign', 160),
                content: $request->input('utm_content', 160),
                term: $request->input('utm_term', 160),
            ),
            ip: $request->clientIp(),
            userAgent: $request->userAgent(),
        );
    }
}
