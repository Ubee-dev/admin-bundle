<?php

namespace UbeeDev\AdminBundle\Form\DataTransformer;

use UbeeDev\LibBundle\Model\Type\Name;
use Symfony\Component\Form\Exception\TransformationFailedException;

class NameTransformer
{
    /**
     * Transform object Name to string for display in form.
     */
    public function transform($value): ?string
    {
        if ($value instanceof Name) {
            return $value->value;
        }

        return null;
    }

    /**
     * Transform string to Name object when form is submitted.
     */
    public function reverseTransform($value): ?Name
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Name::from($value);
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid name address.');
        }
    }
}