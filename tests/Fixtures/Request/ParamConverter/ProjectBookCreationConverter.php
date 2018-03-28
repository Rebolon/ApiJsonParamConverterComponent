<?php

namespace Rebolon\Tests\Fixtures\Request\ParamConverter;

use Rebolon\Request\ParamConverter\ListAbstractConverter;
use Rebolon\Tests\Fixtures\Entity\ProjectBookCreation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectBookCreationConverter extends ListAbstractConverter
{
    const NAME = 'authors';

    const RELATED_ENTITY = ProjectBookCreation::class;

    /**
     * @var AuthorConverter
     */
    protected $authorConverter;

    /**
     * ProjectBookCreationConverter constructor.
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param AuthorConverter $authorConverter
     */
    public function __construct(
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        AuthorConverter $authorConverter
    ) {
        parent::__construct($validator, $serializer, $entityManager);

        $this->authorConverter = $authorConverter;
    }

    /**
     * {@inheritdoc}
     * for this kind of json:
     * {
     *   "authors": { }
     * }
     */
    public function getEzPropsName(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getManyRelPropsName():array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getOneRelPropsName():array
    {
        return [
            'author' => ['converter' => $this->authorConverter, 'registryKey' => 'author', ],
            ];
    }
}
