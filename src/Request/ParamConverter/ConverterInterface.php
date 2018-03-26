<?php

namespace Rebolon\Request\ParamConverter;

use Rebolon\Entity\EntityInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

interface ConverterInterface extends ParamConverterInterface
{
    /**
     * @return string
     */
    public function getIdProperty(): string;

    /**
     * List of accessible properties (int/string/date string converted into date from it's setter per exemple/date/boolean/...)
     *
     * @return array
     */
    public function getEzPropsName(): array;

    /**
     * List of properties that contain sub-entities in a Many-To-Many ways
     *
     * @return array
     */
    public function getManyRelPropsName():array;

    /**
     * List of properties that contain sub-entities in a Many-To-One way
     *
     * @return array
     */
    public function getOneRelPropsName():array;

    /**
     * @param $jsonOrArray
     * @param $propertyPath
     * @return mixed array|EntityInterface
     */
    public function initFromRequest($jsonOrArray, $propertyPath);
}
