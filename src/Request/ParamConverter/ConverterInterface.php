<?php

namespace Rebolon\Request\ParamConverter;

use Rebolon\Entity\EntityInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

interface ConverterInterface extends ParamConverterInterface
{
    /**
     * @return array
     */
    public function getEzPropsName(): array;

    /**
     * @return array
     */
    public function getManyRelPropsName():array;

    /**
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
