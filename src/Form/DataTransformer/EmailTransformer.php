<?php

namespace Khalil1608\AdminBundle\Form\DataTransformer;

use Khalil1608\LibBundle\Model\Type\Email;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EmailTransformer implements DataTransformerInterface
{
    /**
     * Transform object Email to string for display in form.
     */
    public function transform($value): ?string
    {
        if ($value instanceof Email) {
            return $value->value;
        }

        return null;
    }

    /**
     * Transform string to Email object when form is submitted.
     */
    public function reverseTransform($value): ?Email
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Email::from($value);
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid email address.');
        }
    }
}