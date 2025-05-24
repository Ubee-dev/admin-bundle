<?php

namespace Khalil1608\AdminBundle\Form\DataTransformer;

use Khalil1608\LibBundle\Model\Type\Url;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UrlTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        if ($value instanceof Url) {
            return $value->value;
        }

        return null;
    }

    public function reverseTransform($value): ?Url
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Url::from($value);
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid URL.');
        }
    }
}