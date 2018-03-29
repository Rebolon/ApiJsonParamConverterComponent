<?php

namespace Rebolon\Request\ParamConverter;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 *
 * Class ListAbstractConverter
 * @package Rebolon\Request\ParamConverter\Library
 */
abstract class ListAbstractConverter extends AbstractConverter
{
    /**
     * list of entities that represents associative tables can be updated only if they are just after the main root element
     * i can identify it with the property path: book > editors > [x] which means rootEntity > associativeEntities > EntityInList
     * so it's the third item in propertyPath
     */
    const ROOT_LIMIT_FOR_UPDATE = 3;

    /**
     * @inheritdoc
     */
    public function initFromRequest($jsonOrArray, $propertyPath)
    {
        self::$propertyPath[] = $propertyPath;

        $json = $this->checkJsonOrArray($jsonOrArray);

        // the API accept authors as one object or as an array of object, so i need to transform at least in one array
        $listItems = $json;
        if (is_object($json)) {
            $listItems = [$json];
        }

        $entities = [];
        try {
            foreach ($listItems as $item) {
                self::$propertyPath[count(self::$propertyPath)] = '[' . count($entities) . ']';

                $idPropertyIsInJson = false;
                $entity = null;
                if (!is_array($item)
                    || $idPropertyIsInJson = array_key_exists($this->getIdProperty(), $item)
                ) {
                    if (count(self::$propertyPath) > self::ROOT_LIMIT_FOR_UPDATE) {
                        /**
                         * We don't care of other properties. We don't accept update on sub-entity, we can create or re-use
                         * So here we just clean json and replace it with the id content
                         */
                        if ($idPropertyIsInJson) {
                            $item = $item[$this->getIdProperty()];
                        }

                        array_pop(self::$propertyPath);

                        $entity = $this->getFromDatabase($item);
                    }

                    if (!$entity
                        && $idPropertyIsInJson) {
                        $entity = $this->getFromDatabase($item[$this->getIdProperty()], static::RELATED_ENTITY);
                    }
                }

                $entities[] = $this->buildEntity($item, $entity);

                array_pop(self::$propertyPath);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $violationList = new ConstraintViolationList();
            $violation = new ConstraintViolation($e->getMessage(), null, [], null, implode('.', self::$propertyPath), null);
            $violationList->add($violation);
            throw new ValidationException($violationList, sprintf('Wrong parameter to create new %s (generic)', static::RELATED_ENTITY), 420, $e);
        } finally {
            array_pop(self::$propertyPath);
        }

        return $entities;
    }
}
