<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EmbeddingsRequest.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
final class EmbeddingsRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $text,
        public Configuration $embeddingsModel,
    ) {
    }
}
