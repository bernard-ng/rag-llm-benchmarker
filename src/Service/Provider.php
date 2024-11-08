<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Class Provider.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
enum Provider: string
{
    case OPENAI = 'openai';
    case MISTRAL = 'mistral';
    case OLLAMA = 'ollama';
    case GOOGLE = 'google';
}
