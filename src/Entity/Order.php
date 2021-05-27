<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
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
}
