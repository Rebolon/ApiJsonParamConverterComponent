<?php

namespace Rebolon\Tests\Fixtures\Request\ParamConverter;

use Rebolon\Tests\Fixtures\Entity\Serie;
use Rebolon\Request\ParamConverter\AbstractConverter;

class SerieConverter extends AbstractConverter
{
    const NAME = 'serie';

    const RELATED_ENTITY = Serie::class;

    /**
     * {@inheritdoc}
     * for this kind of json:
     * {
     *   "serie": {
     *     "name": "The serie name"
     *   }
     * }
     */
    public function getEzPropsName(): array
    {
        return ['name', ];
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
        return [];
    }
}
