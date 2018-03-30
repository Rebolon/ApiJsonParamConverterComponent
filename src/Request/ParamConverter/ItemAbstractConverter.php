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
     * @inheritdoc
     */
    public function initFromRequest($jsonOrArray, $propertyPath)
    {
        $this->checkMandatoriesImplementations();

        try {
            self::$propertyPath[] = $propertyPath;

            $json = $this->checkJsonOrArray($jsonOrArray);

            $idPropertyIsInJson = false;
            if (!is_array($json)
                || ($idPropertyIsInJson = array_key_exists($this->getIdProperty(), $json))
            ) {
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

            $entity = $this->buildEntity($json);

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
