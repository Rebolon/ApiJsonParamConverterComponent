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
     * @inheritdoc
     */
    public function initFromRequest($jsonOrArray, $propertyPath)
    {
        $this->checkMandatoriesImplementations();

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

                $entities[] = $this->buildEntity($item);

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
