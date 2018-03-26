<?php

namespace Rebolon\Tests\Fixtures\Request\ParamConverter;

use Rebolon\Tests\Fixtures\Entity\Author;
use Rebolon\Request\ParamConverter\AbstractConverter;

class AuthorConverter extends AbstractConverter
{
    const NAME = 'author';

    const RELATED_ENTITY = Author::class;

    /**
     * {@inheritdoc}
     * for this kind of json:
     * {
     *   "author": {
     *     "firstname": "Paul",
     *     "lastname": "Smith"
     *   }
     * }
     */
    public function getEzPropsName(): array
    {
        return ['firstname', 'lastname', ];
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
