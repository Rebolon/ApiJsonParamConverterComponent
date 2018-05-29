<?php
/**
 * run it with phpunit --group git-pre-push
 */
namespace Rebolon\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rebolon\Tests\Fixtures\Entity\Book;
use Rebolon\Tests\Fixtures\Entity\EZBook;
use Rebolon\Tests\Fixtures\Entity\Serie;
use Rebolon\Tests\Fixtures\Request\ParamConverter\BookConverter;
use Rebolon\Tests\Fixtures\Request\ParamConverter\AuthorConverter;
use Rebolon\Tests\Fixtures\Request\ParamConverter\ProjectBookCreationConverter;
use Rebolon\Tests\Fixtures\Request\ParamConverter\SerieConverter;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

/**
 * Class to test Symfony Serializer
 *  One of the first errors i did, came from the JSON input that had a main 'book' property which is not required by the
 *  Serializer => if i remember i also don't need it in my Converter because i deserialize it once to select only the children
 *
 * Class SerializerTest
 * @package Rebolon\Tests
 */
class SerializerTest extends TestCase
{
    /**
     * @var string allow to test a correct HTTP Post with the ability of the ParamConverter to de-duplicate entity like for author in this sample
     */
    public $bodyOkSimple = <<<JSON
{
    "title": "Zombies in western culture"
}
JSON;

    public $bodyOkSimpleWithSerie = <<<JSON
{
    "title": "Zombies in western culture",
    "serie": {
        "id": 4,
        "name": "whatever, it won't be read"
    }
}
JSON;

/**
     * @var string allow to test a correct HTTP Post with the ability of the ParamConverter to de-duplicate entity like for author in this sample
     */
    public $bodyOk = <<<JSON
{
    "book": {
        "title": "Zombies in western culture",
        "authors": [{
            "author": {
                "firstname": "Marc", 
                "lastname": "O'Brien"
            }
        },{
            "author": {
                "firstname": "Marc", 
                "lastname": "O'Brien"
            }
        }, {
            "author": {
                "firstname": "Paul", 
                "lastname": "Kyprianou"
            }
        }],
        "serie": {
            "name": "Open Reports Series"
        }
    }
}
JSON;

    /**
     * @var string to test that the ParamConverter are abled to reuse entity from database
     */
    public $bodyOkWithExistingEntities = <<<JSON
{
    "book": {
        "title": "Oh my god, how simple it is !",
        "serie": 4
    }
}
JSON;

    /**
     * @var string to test that the ParamConverter are abled to reuse entity from database
     */
    public $bodyOkWithExistingEntitiesWithFullProps = <<<JSON
{
    "book": {
        "title": "Oh my god, how simple it is !",
        "serie": {
            "id": 4,
            "name": "whatever, it won't be read"
        }
    }
}
JSON;

    /**
     * @var string allow to test a failed HTTP Post with expected JSON content
     */
public $bodyNoAuthor = <<<JSON
{
    "book": {
        "title": "Oh my god, how simple it is !",
        "authors": [{
            "author": { }
        }]
    }
}
JSON;

    /**
     * @group git-pre-push
     */
    public function testSimpleBook()
    {
        $content = $this->bodyOkSimple;
        $expected = json_decode($content);

        $serializer = new Serializer([
            new DateTimeNormalizer(),
            new ObjectNormalizer(),
        ], [
            new JsonEncoder(),
        ]);

        $book = $serializer->deserialize($content, EZBook::class, 'json');

        $this->assertEquals($expected->title, $book->getTitle());
    }

    /**
     * @group git-pre-push
     */
    public function testWithSerie()
    {
        $content = $this->bodyOkSimpleWithSerie;
        $expected = json_decode($content);

        //@todo test with: use ArrayDenormalizer when getting a list of books in json like described in slide 70 of https://speakerdeck.com/dunglas/mastering-the-symfony-serializer

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

        $logger = $this->createMock(LoggerInterface::class);

        $book = $serializer->deserialize($content, EZBook::class, 'json'/*, [
            'default_constructor_arguments' => [
                'logger' => $logger,
            ]
        ]*/);

        $this->assertEquals($expected->title, $book->getTitle());
        $this->assertEquals($expected->serie->name, $book->getSerie()->getName());

    }

    /**
     * @param $entityManager
     * @return BookConverter|void
     */
    public function getBookConverter($entityManager): BookConverter
    {
        $logger = $this->createMock('\Psr\Log\LoggerInterface');
        $normalizers = [new JsonSerializableNormalizer(), new ArrayDenormalizer(), new ObjectNormalizer(),];
        $validator = Validation::createValidator();
        $jsonEncoder = new JsonEncoder();
        $serializer = new Serializer($normalizers, [$jsonEncoder]);
        foreach ($normalizers as $n) {
            $n->setSerializer($serializer);
        }

        $authorConverter = new AuthorConverter($validator, $serializer, $entityManager);
        $projectBookCreationConverter = new ProjectBookCreationConverter($validator, $serializer, $entityManager, $authorConverter);
        $serieConverter = new SerieConverter($validator, $serializer, $entityManager);
        $bookConverter = new BookConverter($validator, $serializer, $entityManager, $projectBookCreationConverter, $serieConverter, $logger);

        return $bookConverter;
    }
}
