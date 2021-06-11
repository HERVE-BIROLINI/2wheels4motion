<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // /**
    //  * @ORM\Column(type="date")
    //  */
    // private $registration_date;

    // /**
    //  * @ORM\Column(type="decimal", precision=10, scale=0)
    //  */
    // private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $road;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=0)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    // public function getRegistrationDate(): ?\DateTimeInterface
    // {
    //     return $this->registration_date;
    // }

    // public function setRegistrationDate(\DateTimeInterface $registration_date): self
    // {
    //     $this->registration_date = $registration_date;

    //     return $this;
    // }

    // public function getPhone(): ?string
    // {
    //     return $this->phone;
    // }

    // public function setPhone(string $phone): self
    // {
    //     $this->phone = $phone;

    //     return $this;
    // }

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
}
