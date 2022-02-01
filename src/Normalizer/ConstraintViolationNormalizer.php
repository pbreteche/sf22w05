<?php

namespace App\Normalizer;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationNormalizer
{
    public function normalize(ConstraintViolationListInterface $errors): array
    {
        $content = [];
        foreach ($errors as $error) {
            $content[] = [
                'path' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return $content;
    }
}
