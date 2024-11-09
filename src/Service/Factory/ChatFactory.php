<?php

declare(strict_types=1);

namespace App\Service\Factory;

use App\Service\Google\GoogleChat;
use App\Service\Google\GoogleConfig;
use App\Service\Provider;
use LLPhant\Chat\ChatInterface;
use LLPhant\Chat\MistralAIChat;
use LLPhant\Chat\OllamaChat;
use LLPhant\Chat\OpenAIChat;
use LLPhant\OllamaConfig;
use LLPhant\OpenAIConfig;

/**
 * Class ChatFactory.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
abstract readonly class ChatFactory
{
    public static function create(
        Provider $provider,
        OpenAIConfig|OllamaConfig|GoogleConfig $config
    ): ChatInterface {
        return match ($provider) {
            Provider::MISTRAL => new MistralAIChat($config),
            Provider::OPENAI => new OpenAIChat($config),
            Provider::OLLAMA => new OllamaChat($config),
            Provider::GOOGLE => new GoogleChat($config),
        };
    }
}
