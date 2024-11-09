<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Factory\ChatFactory;
use App\Service\Factory\ConfigFactory;
use LLPhant\Chat\ChatInterface;
use LLPhant\Chat\FunctionInfo\FunctionInfo;
use Psr\Http\Message\StreamInterface;

/**
 * Class Chat.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final readonly class Chat implements ChatInterface
{
    private ChatInterface $chat;

    public function __construct(
        Provider $provider = Provider::OPENAI,
        string $model = 'gpt-4-turbo'
    ) {
        $config = ConfigFactory::create($provider, $model);
        $this->chat = ChatFactory::create($provider, $config);
    }

    #[\Override]
    public function generateText(string $prompt): string
    {
        return $this->chat->generateText($prompt);
    }

    #[\Override]
    public function generateTextOrReturnFunctionCalled(string $prompt): string|FunctionInfo
    {
        return $this->chat->generateTextOrReturnFunctionCalled($prompt);
    }

    #[\Override]
    public function generateStreamOfText(string $prompt): StreamInterface
    {
        return $this->chat->generateStreamOfText($prompt);
    }

    #[\Override]
    public function generateChat(array $messages): string
    {
        return $this->chat->generateChat($messages);
    }

    #[\Override]
    public function generateChatStream(array $messages): StreamInterface
    {
        return $this->chat->generateChatStream($messages);
    }

    #[\Override]
    public function setSystemMessage(string $message): void
    {
        $this->chat->setSystemMessage($message);
    }

    #[\Override]
    public function setTools(array $tools): void
    {
        $this->chat->setTools($tools);
    }

    #[\Override]
    public function addTool(FunctionInfo $functionInfo): void
    {
        $this->chat->addTool($functionInfo);
    }

    #[\Override]
    public function setFunctions(array $functions): void
    {
        $this->chat->setFunctions($functions);
    }

    #[\Override]
    public function addFunction(FunctionInfo $functionInfo): void
    {
        $this->chat->addFunction($functionInfo);
    }

    #[\Override]
    public function setModelOption(string $option, mixed $value): void
    {
        $this->chat->setModelOption($option, $value);
    }
}
