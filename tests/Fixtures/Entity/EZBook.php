<?php
namespace Rebolon\Tests\Fixtures\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;
use Rebolon\Entity\EntityInterface;

class EZBook implements EntityInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var Serie
     */
    private $serie;

    /**
     * @var ArrayCollection
     */
    private $testSerie;

    /**
     * @var ArrayCollection
     */
    private $authors;

    /**
     * @var LoggerInterface
     */
    //private $logger;

    /**
     * Book constructor.
     * The params is only for sample purpose
     *
     * @param LoggerInterface $logger
     */
    public function __construct(/*LoggerInterface $logger*/)
    {
        $this->authors = new ArrayCollection();
        $this->testSerie = new ArrayCollection();
        //$this->logger = $logger;
    }

    /**
     * id can be null until flush is done
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Book
     */
    public function setTitle($title): EZBook
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Serie
     */
    public function getSerie(): ?Serie
    {
        return $this->serie;
    }

    /**
     * @param Serie $serie
     *
     * @return Book
     */
    public function setSerie(Serie $serie): EZBook
    {
        $this->serie = $serie;

        return $this;
    }

    /**
     * @param ProjectBookCreation $project
     *
     * @return Book
     */
    public function setAuthor(ProjectBookCreation $project): EZBook
    {
        $this->authors[] = $project;

        return $this;
    }

    /**
     * @param Author $author
     * @param Job $job
     * @return Book
     */
    public function addAuthor(Author $author, Job $job): EZBook
    {
        $project = (new ProjectBookCreation())
            ->setBook($this)
            ->setAuthor($author)
            ->setRole($job);

        // @test this feature to check that it really works vs if ($this->projectBookCreation->contains($project)) return $this;
        foreach ($this->authors as $projectToCheck) {
            if ($projectToCheck->getAuthor() === $author
                && $projectToCheck->role === $job) {
                return $this;
            }
        }

        $this->setAuthor($project);

        return $this;
    }

    /**
     * Return the list of Authors with their job for this project book creation
     *
     * @return Collection
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    /**
     * @param Serie $serie
     *
     * @return EZBook
     */
    public function addTestSerie(Serie $serie): EZBook
    {
        $this->testSerie[] = $serie;

        return $this;
    }

    /**
     * @param ArrayCollection $series
     *
     * @return EZBook
     * @throws \Exception
     */
    public function setTestSerie(array $series): EZBook
    {
        foreach ($series as $serie) {
            if (!$serie instanceof Serie) {
                throw new \Exception('must be a Serie');
            }

            $this->setAuthor($serie);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTestSerie(): ArrayCollection
    {
        return $this->testSerie;
    }

    /**
     * @param LoggerInterface $logger
     * @return Book
     */
//    public function setLogger(LoggerInterface $logger): Book
//    {
//        $this->logger = $logger;
//
//        return $this;
//    }
}
