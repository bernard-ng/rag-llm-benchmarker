<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Model;
use App\Service\Provider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Trait ConfigSelectionTrait.
 *
 * @author bernard-ng <bernard@devscast.tech>
 */
trait ConfigSelectionTrait
{
    /**
     * @return array{Provider, Model, boolean}
     */
    public function selectModel(Request $request): array
    {
        $provider = $request->query->getString('provider', 'openai');
        $model = $request->query->getString('model', 'gpt-3.5-turbo');
        $context = $request->query->getBoolean('context', true);

        try {
            return [Provider::from($provider), Model::from($model), $context];
        } catch (\Throwable) {
            throw new BadRequestHttpException('Invalid provider or model');
        }
    }
}
