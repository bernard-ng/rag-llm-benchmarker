<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CompletionRequest.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class CompletionRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $prompt,
        public Configuration $generativeModel,
        public Configuration $embeddingsModel,
        public bool $useContext = false,
    ) {
    }
}
