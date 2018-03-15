<?php

namespace Rebolon\Tests\Fixtures\Request\ParamConverter;

use Doctrine\ORM\EntityManagerInterface;
use Rebolon\Request\ParamConverter\AbstractConverter;
use Rebolon\Tests\Fixtures\Entity\Book;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ConcreteConverter
 *
 * @package Rebolon\Request\ParamConverter
 */
class BookConverter extends AbstractConverter
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
     */
    public function __construct(
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ProjectBookCreationConverter $projectBookCreationConverter,
        SerieConverter $serieConverter
    ) {
        parent::__construct($validator, $serializer, $entityManager);

        $this->projectBookCreationConverter = $projectBookCreationConverter;
        $this->serieConverter = $serieConverter;
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
        return ['id', 'title', ];
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
