<?php

namespace App\Entity;

use App\Repository\TenderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TenderRepository::class)
 */
class Tender
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
    private $tendernumber;

    /**
     * @ORM\Column(type="datetime")
     */
    private $tender_datetime;

    /**
     * @ORM\Column(type="time")
     */
    private $rdvat_time;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comments;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity=Claim::class, inversedBy="tender")
     * @ORM\JoinColumn(nullable=false)
     */
    private $claim;

    /**
     * @ORM\ManyToMany(targetEntity=Driver::class, mappedBy="tenders")
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

    public function getTendernumber(): ?string
    {
        return $this->tendernumber;
    }

    public function setTendernumber(string $tendernumber): self
    {
        $this->tendernumber = $tendernumber;

        return $this;
    }

    public function getTenderDatetime(): ?\DateTimeInterface
    {
        return $this->tender_datetime;
    }

    public function setTenderDatetime(\DateTimeInterface $tender_datetime): self
    {
        $this->tender_datetime = $tender_datetime;

        return $this;
    }

    public function getRdvatTime(): ?\DateTimeInterface
    {
        return $this->rdvat_time;
    }

    public function setRdvatTime(\DateTimeInterface $rdvat_time): self
    {
        $this->rdvat_time = $rdvat_time;

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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getClaim(): ?Claim
    {
        return $this->claim;
    }

    public function setClaim(?Claim $claim): self
    {
        $this->claim = $claim;

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
            $driver->addTender($this);
        }

        return $this;
    }

    public function removeDriver(Driver $driver): self
    {
        if ($this->drivers->removeElement($driver)) {
            $driver->removeTender($this);
        }

        return $this;
    }
}
