<?php

declare(strict_types=1);

namespace App\Request;

use App\Service\Provider;

/**
 * Class ModelConfiguration.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
class Config
{
    public function __construct(
        public Provider $provider = Provider::OPENAI,
        public string $model = 'gpt-4-turbo',
    ) {
    }
}
