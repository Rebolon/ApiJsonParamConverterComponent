<?php

namespace Rebolon\Tests\Fixtures\Request\ParamConverter;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Rebolon\Tests\Fixtures\Entity\ProjectBookCreation;
use Rebolon\Request\ParamConverter\AbstractConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProjectBookCreationConverter extends AbstractConverter
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

    /**
     * @inheritdoc
     */
    public function initFromRequest($jsonOrArray, $propertyPath)
    {
        self::$propertyPath[] = $propertyPath;

        $json = $this->checkJsonOrArray($jsonOrArray);

        // the API accept authors as one object or as an array of object, so i need to transform at least in one array
        $authors = $json;
        if (is_object($json)) {
            $authors = [$json];
        }

        $entities = [];
        try {
            foreach ($authors as $author) {
                self::$propertyPath[count(self::$propertyPath)] = '[' . count($entities) . ']';

                $entities[] = $this->buildEntity($author);

                array_pop(self::$propertyPath);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $violationList = new ConstraintViolationList();
            $violation = new ConstraintViolation($e->getMessage(), null, [], null, implode('.', self::$propertyPath), null);
            $violationList->add($violation);
            throw new ValidationException($violationList, 'Wrong parameter to create new Authors (generic)', 420, $e);
        } finally {
            array_pop(self::$propertyPath);
        }

        return $entities;
    }
}
