<?php

declare(strict_types=1);

namespace App\Service\Google;

use GeminiAPI\Client;
use GeminiAPI\Enums\ModelName;
use GeminiAPI\Resources\Parts\TextPart;
use LLPhant\Chat\ChatInterface;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class GoogleChat.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class GoogleChat implements ChatInterface
{
    private readonly Client $client;

    private string $systemMessage;

    public function __construct(
        private readonly GoogleConfig $config
    ) {
        $this->client = new Client($config->apiKey);
    }

    /**
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function generateText(string $prompt): string
    {
        $response = $this->client
            ->generativeModel(ModelName::from("models/{$this->config->model}"))
            ->withSystemInstruction($this->systemMessage)
            ->generateContent(new TextPart($prompt));

        return $response->text();
    }

    #[\Override]
    public function setSystemMessage(string $message): void
    {
        $this->systemMessage = $message;
    }

    #[\Override]
    public function generateStreamOfText(string $prompt): StreamInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function generateChat(array $messages): string
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function generateChatStream(array $messages): StreamInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function setModelOption(string $option, mixed $value): void
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function setTools(array $tools): void
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function addTool(FunctionInfo $functionInfo): void
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function setFunctions(array $functions): void
    {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function addFunction(FunctionInfo $functionInfo): void
    {
        throw new \RuntimeException('Not implemented');
    }
}
