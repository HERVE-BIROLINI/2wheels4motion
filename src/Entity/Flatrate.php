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
     * @ORM\Column(type="decimal", precision=6, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pickup_included;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private $region_code;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
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

    public function getRegionCode(): ?string
    {
        return $this->region_code;
    }

    public function setRegionCode(?string $region_code): self
    {
        $this->region_code = $region_code;

        return $this;
    }
}
