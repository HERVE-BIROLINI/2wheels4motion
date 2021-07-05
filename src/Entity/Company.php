<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CompanyRepository::class)
 */
class Company
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
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=0)
     */
    private $siren;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=0, nullable=true)
     */
    private $nic;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $road;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $city;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isconfirmed;

    /**
     * @ORM\OneToMany(targetEntity=Driver::class, mappedBy="company")
     */
    private $drivers;

    public function __construct()
    {
        $this->drivers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSiren(): ?string
    {
        return $this->siren;
    }

    public function setSiren(string $siren): self
    {
        $this->siren = $siren;

        return $this;
    }

    public function getNic(): ?string
    {
        return $this->nic;
    }

    public function setNic(string $nic): self
    {
        $this->nic = $nic;

        return $this;
    }

    public function getRoad(): ?string
    {
        return $this->road;
    }

    public function setRoad(string $road): self
    {
        $this->road = $road;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getIsconfirmed(): ?bool
    {
        return $this->isconfirmed;
    }

    public function setIsconfirmed(?bool $isconfirmed): self
    {
        $this->isconfirmed = $isconfirmed;

        return $this;
    }

    /**
     * @return Collection|Driver[]
     */
    public function getDrivers(): Collection
    {
        return $this->drivers;
    }

    public function addDriver(Driver $driver): self
    {
        if (!$this->drivers->contains($driver)) {
            $this->drivers[] = $driver;
            $driver->setCompany($this);
        }

        return $this;
    }

    public function removeDriver(Driver $driver): self
    {
        if ($this->drivers->removeElement($driver)) {
            // set the owning side to null (unless already changed)
            if ($driver->getCompany() === $this) {
                $driver->setCompany(null);
            }
        }

        return $this;
    }
}