<?php

namespace App\Entity;

use App\Repository\DriverRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DriverRepository::class)
 */
class Driver
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $registration_date;

    /**
     * @ORM\Column(type="date")
     */
    private $vmdtr_validity;

    /**
     * @ORM\Column(type="decimal", precision=11, scale=0)
     */
    private $vmdtr_number;

    /**
     * @ORM\Column(type="boolean")
     */
    private $agreeterms;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $subscription_validity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registration_date;
    }

    public function setRegistrationDate(\DateTimeInterface $registration_date): self
    {
        $this->registration_date = $registration_date;

        return $this;
    }

    public function getVmdtrValidity(): ?\DateTimeInterface
    {
        return $this->vmdtr_validity;
    }

    public function setVmdtrValidity(\DateTimeInterface $vmdtr_validity): self
    {
        $this->vmdtr_validity = $vmdtr_validity;

        return $this;
    }

    public function getVmdtrNumber(): ?string
    {
        return $this->vmdtr_number;
    }

    public function setVmdtrNumber(string $vmdtr_number): self
    {
        $this->vmdtr_number = $vmdtr_number;

        return $this;
    }

    public function getAgreeterms(): ?bool
    {
        return $this->agreeterms;
    }

    public function setAgreeterms(bool $agreeterms): self
    {
        $this->agreeterms = $agreeterms;

        return $this;
    }

    public function getSubscriptionValidity(): ?\DateTimeInterface
    {
        return $this->subscription_validity;
    }

    public function setSubscriptionValidity(?\DateTimeInterface $subscription_validity): self
    {
        $this->subscription_validity = $subscription_validity;

        return $this;
    }
}
