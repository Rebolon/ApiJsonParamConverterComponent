<?php

namespace Rebolon\Request\ParamConverter;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 *
 * Class ItemAbstractConverter
 * @package Rebolon\Request\ParamConverter\Library
 */
abstract class ItemAbstractConverter extends AbstractConverter
{
    /**
     * only root element can be updated, all nested entities won't be even if they have ID. All other fields of thoses entities will be forgiven
     */
    const ROOT_LIMIT_FOR_UPDATE = 1;

    /**
     * @inheritdoc
     */
    public function initFromRequest($jsonOrArray, $propertyPath)
    {
        try {
            self::$propertyPath[] = $propertyPath;

            $json = $this->checkJsonOrArray($jsonOrArray);

            $idPropertyIsInJson = false;
            $entity = null;
            if (!is_array($json)
                || ($idPropertyIsInJson = array_key_exists($this->getIdProperty(), $json))
            ) {

                if (count(self::$propertyPath) > self::ROOT_LIMIT_FOR_UPDATE) {
                    /**
                     * We don't care of other properties. We don't accept update on sub-entity, we can create or re-use
                     * So here we just clean json and replace it with the id content
                     */
                    if ($idPropertyIsInJson) {
                        $json = $json[$this->getIdProperty()];
                    }

                    array_pop(self::$propertyPath);

                    return $this->getFromDatabase($json);
                }

                if ($idPropertyIsInJson) {
                    $entity = $this->getFromDatabase($json[$this->getIdProperty()], static::RELATED_ENTITY);
                }
            }

            $entity = $this->buildEntity($json, $entity);

            array_pop(self::$propertyPath);

            return $entity;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            $violationList = new ConstraintViolationList();
            $violation = new ConstraintViolation($e->getMessage(), null, [], null, $this->getPropertyPath(), null);
            $violationList->add($violation);
            throw new ValidationException(
                $violationList,
                sprintf('Wrong parameter to create new %s (generic)', static::RELATED_ENTITY),
                420,
                $e
            );
        }
    }
}
