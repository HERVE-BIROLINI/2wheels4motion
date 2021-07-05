<?php

namespace App\Entity;

use App\Repository\FlatrateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FlatrateRepository::class)
 */
class Flatrate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pickup_included;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $department_code;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPickupIncluded(): ?bool
    {
        return $this->pickup_included;
    }

    public function setPickupIncluded(bool $pickup_included): self
    {
        $this->pickup_included = $pickup_included;

        return $this;
    }

    public function getDepartmentCode(): ?string
    {
        return $this->department_code;
    }

    public function setDepartmentCode(?string $department_code): self
    {
        $this->department_code = $department_code;

        return $this;
    }
}
