<?php

namespace Rebolon\Tests\Fixtures\Normalizer;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Rebolon\Tests\Fixtures\Entity\EZBook;
use Rebolon\Tests\Fixtures\Entity\Serie;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Serializer;

class TestSerie extends ObjectNormalizer implements DenormalizerInterface
{
    public function denormalize($object, $class, $format = null, array $context = array())
    {
        $classMetaDataFactory = new ClassMetadataFactory(
            new AnnotationLoader(
                new AnnotationReader()
            )
        );
        $objectNormalizer = new ObjectNormalizer($classMetaDataFactory, null, null, new PhpDocExtractor());
        $serializer = new Serializer([
            new DateTimeNormalizer(),
            $objectNormalizer,
        ], [
            new JsonEncoder(),
        ]);

        foreach ($object->test_serie as $serie) {
            $object->addTestSerie($serializer->deserialize(json_encode($serie), Serie::class));
        }

        return $object;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return \Rebolon\Tests\Fixtures\Entity\Serie::class === $type;
    }
}