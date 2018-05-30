<?php

namespace Rebolon\Tests\Fixtures\Normalizer;

use Rebolon\Tests\Fixtures\Entity\EZBook;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProjectBookCreation extends ObjectNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        return [
            'project_book_creation' => $object->authors,
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof EZBook;
    }
}