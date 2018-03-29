<?php
/**
 * run it with phpunit --group git-pre-push
 */
namespace Rebolon\Tests;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Rebolon\Tests\Fixtures\Entity\Serie;
use Rebolon\Tests\Fixtures\Request\ParamConverter\BookConverter;
use Rebolon\Tests\Fixtures\Request\ParamConverter\AuthorConverter;
use Rebolon\Tests\Fixtures\Request\ParamConverter\ProjectBookCreationConverter;
use Rebolon\Tests\Fixtures\Request\ParamConverter\SerieConverter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

class ApiJsonParamConverterTest extends TestCase
{
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
    public function testWithAllEntitiesToBeCreatedExcept2AuthorsInsteadOf3()
    {
        $content = json_decode($this->bodyOk);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $bookConverter = $this->getBookConverter($entityManager);

        $bookConverter->setInsertMode();
        $book = $bookConverter->initFromRequest(json_encode($content->book), 'book');

        $this->assertEquals($content->book->title, $book->getTitle());
        $this->assertEquals($content->book->serie->name, $book->getSerie()->getName());
        $this->assertCount(3, $book->getAuthors());

        $this->assertEquals($content->book->authors[0]->author->firstname, $book->getAuthors()[0]->getAuthor()->getFirstname());
        $this->assertEquals($content->book->authors[0]->author->lastname, $book->getAuthors()[0]->getAuthor()->getLastname());

        $this->assertEquals($content->book->authors[2]->author->firstname, $book->getAuthors()[2]->getAuthor()->getFirstname());
        $this->assertEquals($content->book->authors[2]->author->lastname, $book->getAuthors()[2]->getAuthor()->getLastname());

        // check that there is only 2 different Authors
        $this->assertEquals($book->getAuthors()[0], $book->getAuthors()[1]);
        $this->assertNotEquals($book->getAuthors()[1], $book->getAuthors()[2]);
    }

    /**
     * @group git-pre-push
     */
    public function testWithExistingEntity()
    {
        $content = json_decode($this->bodyOkWithExistingEntities);

        $serie = new Serie();
        $serie->setName('Harry Potter');

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($serie));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $bookConverter = $this->getBookConverter($entityManager);

        $bookConverter->setInsertMode();
        $book = $bookConverter->initFromRequest(json_encode($content->book), 'book');

        $this->assertEquals($content->book->title, $book->getTitle());
        $this->assertEquals($serie->getName(), $book->getSerie()->getName());
    }

    /**
     * @group git-pre-push
     */
    public function testWithExistingEntityButWithFullProps()
    {
        $content = json_decode($this->bodyOkWithExistingEntitiesWithFullProps);

        $serie = new Serie();
        $serie->setName('Harry Potter');

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->any())
            ->method('find')
            ->will($this->returnValue($serie));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $bookConverter = $this->getBookConverter($entityManager);

        $bookConverter->setInsertMode();
        $book = $bookConverter->initFromRequest(json_encode($content->book), 'book');

        $this->assertEquals($content->book->title, $book->getTitle());
        $this->assertEquals($serie->getName(), $book->getSerie()->getName());
    }

    /**
     * @group git-pre-push
     * @expectedException        \ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException
     * @expectedExceptionMessage Wrong parameter to create new Rebolon\Tests\Fixtures\Entity\Author (generic)
     */
    public function testWithWrongJson()
    {
        $content = json_decode($this->bodyNoAuthor);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $bookConverter = $this->getBookConverter($entityManager);

        $bookConverter->setInsertMode();
        $bookConverter->initFromRequest(json_encode($content->book), 'book');
    }

    /**
     * @param $entityManager
     * @return BookConverter|void
     */
    public function getBookConverter($entityManager): BookConverter
    {
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
        $bookConverter = new BookConverter($validator, $serializer, $entityManager, $projectBookCreationConverter, $serieConverter);

        return $bookConverter;
    }
}
