<?php
namespace Rebolon\Tests\Fixtures\Entity;

use Rebolon\Entity\EntityInterface;

class ProjectBookCreation implements EntityInterface
{
    private $id;

    private $role;

    private $book;

    private $author;

    /**
     * mandatory for api-platform to get a valid IRI
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Job
     */
    public function getRole(): Job
    {
        return $this->role;
    }

    /**
     * @param Job $role
     * @return ProjectBookCreation
     */
    public function setRole(Job $role): ProjectBookCreation
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Book
     */
    public function getBook(): Book
    {
        return $this->book;
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function setBook(Book $book): ProjectBookCreation
    {
        $this->book = $book;

        return $this;
    }

    /**
     * @return Author
     */
    public function getAuthor(): Author
    {
        return $this->author;
    }

    /**
     * @param Author $author
     * @return $this
     */
    public function setAuthor(Author $author): ProjectBookCreation
    {
        $this->author = $author;

        return $this;
    }
}
