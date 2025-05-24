<?php

namespace Khalil1608\AdminBundle\Form\DataTransformer;

use Khalil1608\LibBundle\Model\Type\PhoneNumber;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        if ($value instanceof PhoneNumber) {
            return $value->__toString();
        }

        return null;
    }

    public function reverseTransform($value): ?PhoneNumber
    {
        if (empty($value)) {
            return null;
        }

        try {
            return PhoneNumber::from($value);
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid phone number.');
        }
    }
}