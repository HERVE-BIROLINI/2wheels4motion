<?php

namespace App\Entity;

use App\Repository\EvaluationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EvaluationRepository::class)
 */
class Evaluation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $evaluation_datetime;

    /**
     * @ORM\Column(type="decimal", precision=1, scale=1)
     */
    private $score;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comments;

    /**
     * @ORM\Column(type="boolean")
     */
    private $moderated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluationDatetime(): ?\DateTimeInterface
    {
        return $this->evaluation_datetime;
    }

    public function setEvaluationDatetime(\DateTimeInterface $evaluation_datetime): self
    {
        $this->evaluation_datetime = $evaluation_datetime;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(string $comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getModerated(): ?bool
    {
        return $this->moderated;
    }

    public function setModerated(bool $moderated): self
    {
        $this->moderated = $moderated;

        return $this;
    }
}
