<?php

namespace Rebolon\Tests\Fixtures\Request\ParamConverter;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Rebolon\Request\ParamConverter\ItemAbstractConverter;
use Rebolon\Tests\Fixtures\Entity\Book;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ConcreteConverter
 *
 * @package Rebolon\Request\ParamConverter
 */
class BookConverter extends ItemAbstractConverter
{
    const NAME = 'book';

    const RELATED_ENTITY = Book::class;

    /**
     * @var ProjectBookCreationConverter
     */
    protected $projectBookCreationConverter;

    /**
     * @var SerieConverter
     */
    protected $serieConverter;

    /**
     * ConcreteConverter constructor.
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param ProjectBookCreationConverter $projectBookCreationConverter
     * @param SerieConverter $serieConverter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ProjectBookCreationConverter $projectBookCreationConverter,
        SerieConverter $serieConverter,
        LoggerInterface $logger
    ) {
        parent::__construct($validator, $serializer, $entityManager);

        $this->projectBookCreationConverter = $projectBookCreationConverter;
        $this->serieConverter = $serieConverter;

        $this->constructorParams[] = $logger;
    }

    /**
     * {@inheritdoc}
     * for this kind of json:
     * {
     *   "book": {
     *     "title": "The green lantern",
     *   }
     * }
     */
    public function getEzPropsName(): array
    {
        return ['title', ];
    }

    /**
     * {@inheritdoc}
     */
    public function getManyRelPropsName():array
    {
        return [
            'authors' => [
                'converter' => $this->projectBookCreationConverter,
                'setter' => 'setAuthor',
                'cb' => function ($relation, $entity) {
                    $relation->setBook($entity);
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getOneRelPropsName():array
    {
        return [
            'serie' => [
                'converter' => $this->serieConverter,
                'registryKey' => 'serie',
                'setter' => 'setSerie',
            ],
        ];
    }
}
