<?php
/**
 * run it with phpunit --group git-pre-push
 */
namespace Rebolon\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Rebolon\Tests\Fixtures\Entity\Book;
use Rebolon\Tests\Fixtures\Entity\EZBook;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class to test Symfony Serializer
 *  One of the first errors i did, came from the JSON input that had a main 'book' property which is not required by the
 *  Serializer => if i remember i also don't need it in my Converter because i deserialize it once to select only the children
 *
 * Class SerializerBookTest
 * @package Rebolon\Tests
 */
class SerializerBookTest extends TestCase
{
    /**
     * @var string allow to test a correct HTTP Post with the ability of the ParamConverter to de-duplicate entity like for author in this sample
     */
    public $bookOkSimple = <<<JSON
{
    "title": "Zombies in western culture"
}
JSON;

    public $boookOkSimpleWithSerie = <<<JSON
{
    "title": "Zombies in western culture",
    "serie": {
        "id": 4,
        "name": "whatever, it won't be read"
    }
}
JSON;

    public $bookOkSimpleWithCollectionOfReview = <<<JSON
{
    "title": "Zombies in western culture",
    "reviews": [{
        "id": 4,
        "content": "this book is so cool",
        "date": "2018-05-17T00:00:00+00:00",
        "username": "Joe654"
    }, {
        "id": 5,
        "content": "hey it's awesome !",
        "date": "2018-05-22T00:00:00+00:00",
        "username": "Niko9342"
    }]
}
JSON;

/**
     * @var string allow to test a correct HTTP Post with the ability of the ParamConverter to de-duplicate entity like for author in this sample
     */
    public $bookOkSimpleWithAuthor = <<<JSON
{
    "title": "Zombies in western culture",
    "authors": [{
        "job": {
            "translation": "writer"
        },
        "author": {
            "firstname": "Marc", 
            "lastname": "O'Brien"
        }
    }]
}
JSON;

    /**
     * @var string to test that the ParamConverter are abled to reuse entity from database
     */
    public $bookOkWithExistingEntities = <<<JSON
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
    public $bookOkWithExistingEntitiesWithFullProps = <<<JSON
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
public $bookNoAuthor = <<<JSON
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
     * deserialize a simple json into a simple EZBook: only title props
     */
    public function testSimpleBook()
    {
        $content = $this->bookOkSimple;
        $expected = json_decode($content);

        $serializer = new Serializer([
            new ObjectNormalizer(),
        ], [
            new JsonEncoder(),
        ]);

        $book = $serializer->deserialize($content, EZBook::class, 'json');

        $this->assertEquals($expected->title, $book->getTitle());
    }

    /**
     * deserialize a more complex json with a serie inside the book
     */
    public function testWithSerie()
    {
        $content = $this->boookOkSimpleWithSerie;
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

        $book = $serializer->deserialize($content, EZBook::class, 'json', [
            'default_constructor_arguments' => [
                EZBook::class => ['logger' => $logger, ]
            ],
        ]);

        $this->assertEquals($expected->title, $book->getTitle());
        $this->assertEquals($expected->serie->name, $book->getSerie()->getName());

    }

    /**
     * deserialize a really more complex json with an array of serie inside the testSerie property
     *
     * This test fail: i want to get a book with a collection of Serie in property testSerie
     * For instance when the Serializer call the setTestSerie it sends the array of serie, but it doesn't contain an
     * array of serie but an array of array when keys are props of serie, they are not yet deserialized into Serie
     *
     * @group git-pre-push
     */
    public function testWithCollectionOfReview()
    {
        $content = $this->bookOkSimpleWithCollectionOfReview;
        $expected = json_decode($content);

        //@todo test with: use ArrayDenormalizer when getting a list of books in json like described in slide 70 of https://speakerdeck.com/dunglas/mastering-the-symfony-serializer
        $classMetaDataFactory = new ClassMetadataFactory(
            new AnnotationLoader(
                new AnnotationReader()
            )
        );
        $objectNormalizer = new ObjectNormalizer($classMetaDataFactory, null, null, new PhpDocExtractor());
        $serializer = new Serializer([
            new ArrayDenormalizer(),
            new DateTimeNormalizer(),
            $objectNormalizer,
        ], [
            new JsonEncoder(),
        ]);

        $book = $serializer->deserialize($content, EZBook::class, 'json');

        $this->assertEquals($expected->title, $book->getTitle());
        foreach ($expected->reviews as $k => $review) {
            $this->assertEquals($review->id, $book->getReviews()[$k]->getId());
            $this->assertEquals($review->content, $book->getReviews()[$k]->getContent());
            $this->assertEquals($review->date, $book->getReviews()[$k]->getDate()->format('c'));
            $this->assertEquals($review->username, $book->getReviews()[$k]->getUsername());
        }

    }
}
