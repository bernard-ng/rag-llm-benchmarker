<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Google\GoogleConfig;
use LLPhant\OllamaConfig;
use LLPhant\OpenAIConfig;

/**
 * Class Config.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
abstract readonly class Config
{
    public static function get(Provider $service, ?Model $model = null): OpenAIConfig|OllamaConfig|GoogleConfig|null
    {
        switch ($service) {
            case Provider::OPENAI:
                $openai = new OpenAIConfig();
                $openai->apiKey = $_ENV['OPENAI_API_KEY'];
                $openai->model = $model ? $model->value : Model::GPT35_TURBO->value;
                return $openai;
            case Provider::MISTRAL:
                $mistral = new OpenAIConfig();
                $mistral->apiKey = $_ENV['MISTRAL_API_KEY'];
                return $mistral;
            case Provider::OLLAMA:
                $ollama = new OllamaConfig();
                $ollama->model = $model ? $model->value : Model::MISTRAL_7B->value;
                $ollama->url = $_ENV['OLLAMA_SERVER_URL'] ?? 'http://localhost:11434/api/';
                return $ollama;
            case Provider::GOOGLE:
                $google = new GoogleConfig();
                $google->apiKey = $_ENV['GEMINI_API_KEY'];
                $google->model = $model ? $model->value : Model::GEMINI_PRO->value;
                return $google;
            default:
                throw new \InvalidArgumentException('invalid or unsupported provider');
        }
    }
}
