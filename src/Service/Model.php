<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Class Model.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
enum Model: string
{
    // google provider
    case GEMINI_PRO = 'gemini-1.5-pro';

    // openai provider
    case GPT4_TURBO = 'gpt-4-turbo';
    case GPT35_TURBO = 'gpt-3.5-turbo-0125';

    // mistral provider
    case MISTRAL_LARGE = 'mistral-large-2407';

    // ollama provider
    case LLAMA2_7B = 'llama2';
    case MISTRAL_7B = 'mistral';
    case VICUNA = 'vicuna';
}
