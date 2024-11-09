<?php

declare(strict_types=1);

namespace App\Service\Factory;

use App\Service\Google\GoogleConfig;
use App\Service\Provider;
use LLPhant\OllamaConfig;
use LLPhant\OpenAIConfig;

/**
 * Class ConfigFactory.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
abstract readonly class ConfigFactory
{
    public static function create(Provider $service, ?string $model = null): OpenAIConfig|OllamaConfig|GoogleConfig
    {
        switch ($service) {
            case Provider::OPENAI:
                $openai = new OpenAIConfig();
                $openai->apiKey = $_ENV['OPENAI_API_KEY'];
                $openai->model = $model ?? 'gpt-4-turbo';
                return $openai;
            case Provider::MISTRAL:
                $mistral = new OpenAIConfig();
                $mistral->apiKey = $_ENV['MISTRAL_API_KEY'];
                return $mistral;
            case Provider::OLLAMA:
                $ollama = new OllamaConfig();
                $ollama->model = $model ?? 'mistral';
                $ollama->url = $_ENV['OLLAMA_SERVER_URL'] ?? 'http://localhost:11434/api/';
                return $ollama;
            case Provider::GOOGLE:
                $google = new GoogleConfig();
                $google->apiKey = $_ENV['GEMINI_API_KEY'];
                $google->model = $model ?? 'gemini-1.5-pro';
                return $google;
        }
    }
}
