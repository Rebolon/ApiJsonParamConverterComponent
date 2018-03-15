<?php
namespace Rebolon\Tests\Fixtures\Entity;

use Rebolon\Entity\EntityInterface;

class Serie implements EntityInterface
{
    private $id;

    private $name;

    /**
     * id can be null until flush is done
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Serie
     */
    public function setName($name): Serie
    {
        $this->name = $name;

        return $this;
    }
}
