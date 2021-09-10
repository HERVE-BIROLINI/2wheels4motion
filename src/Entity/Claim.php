<?php

namespace App\Entity;

use App\Repository\ClaimRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClaimRepository::class)
 */
class Claim
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
    private $claim_datetime;

    /**
     * @ORM\Column(type="date")
     */
    private $journey_date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $from_road;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $from_zip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $to_road;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $to_zip;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $priority_departure;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $departureat_time;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $arrivalat_time;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $from_city;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $to_city;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="claims")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToMany(targetEntity=Driver::class, inversedBy="drivers")
     */
    private $drivers;

    /**
     * @ORM\OneToMany(targetEntity=Tender::class, mappedBy="claim")
     */
    private $tenders;

    /**
     * @ORM\Column(type="time")
     */
    private $journey_time;

    /**
     * @ORM\ManyToOne(targetEntity=Remarkableplace::class, inversedBy="claims_from")
     */
    private $remarkableplace_from;

    /**
     * @ORM\ManyToOne(targetEntity=Remarkableplace::class, inversedBy="claims_to")
     */
    private $remarkableplace_to;

    /**
     * @ORM\ManyToOne(targetEntity=Flatrate::class)
     */
    private $flatrate;

    /**
     * @ORM\OneToMany(targetEntity=ClaimStatus::class, mappedBy="claim", orphanRemoval=true)
     */
    private $claimStatuses;

    public function __construct()
    {
        $this->drivers = new ArrayCollection();
        $this->tenders = new ArrayCollection();
        $this->claimStatuses = new ArrayCollection();
    }

    //
    public function getId(): ?int
    {
        return $this->id;
    }
    //
    public function getClaimDatetime(): ?\DateTimeInterface
    {
        return $this->claim_datetime;
    }
    public function setClaimDatetime(\DateTimeInterface $claim_datetime): self
    {
        $this->claim_datetime = $claim_datetime;
        return $this;
    }
    //
    public function getJourneyDate(): ?\DateTimeInterface
    {
        return $this->journey_date;
    }
    public function setJourneyDate(\DateTimeInterface $journey_date): self
    {
        $this->journey_date = $journey_date;
        return $this;
    }
    //
    public function getFromRoad(): ?string
    {
        return $this->from_road;
    }
    public function setFromRoad(string $from_road): self
    {
        $this->from_road = $from_road;
        return $this;
    }
    //
    public function getFromZip(): ?string
    {
        return $this->from_zip;
    }
    public function setFromZip(string $from_zip): self
    {
        $this->from_zip = $from_zip;
        return $this;
    }
    //
    public function getToRoad(): ?string
    {
        return $this->to_road;
    }
    public function setToRoad(string $to_road): self
    {
        $this->to_road = $to_road;
        return $this;
    }
    //
    public function getToZip(): ?string
    {
        return $this->to_zip;
    }
    public function setToZip(string $to_zip): self
    {
        $this->to_zip = $to_zip;
        return $this;
    }
    //
    public function getPriorityDeparture(): ?bool
    {
        return $this->priority_departure;
    }
    public function setPriorityDeparture(?bool $priority_departure): self
    {
        $this->priority_departure = $priority_departure;
        return $this;
    }
    //
    public function getDepartureatTime(): ?\DateTimeInterface
    {
        return $this->departureat_time;
    }
    public function setDepartureatTime(?\DateTimeInterface $departureat_time): self
    {
        $this->departureat_time = $departureat_time;
        return $this;
    }
    //
    public function getArrivalatTime(): ?\DateTimeInterface
    {
        return $this->arrivalat_time;
    }
    public function setArrivalatTime(\DateTimeInterface $arrivalat_time): self
    {
        $this->arrivalat_time = $arrivalat_time;
        return $this;
    }
    //
    public function getComments(): ?string
    {
        return $this->comments;
    }
    public function setComments(string $comments): self
    {
        $this->comments = $comments;
        return $this;
    }
    //
    public function getFromCity(): ?string
    {
        return $this->from_city;
    }
    public function setFromCity(string $from_city): self
    {
        $this->from_city = $from_city;
        return $this;
    }
    //
    public function getToCity(): ?string
    {
        return $this->to_city;
    }
    public function setToCity(string $to_city): self
    {
        $this->to_city = $to_city;
        return $this;
    }
    //
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }
    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
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
        }
        return $this;
    }
    public function removeDriver(Driver $driver): self
    {
        $this->drivers->removeElement($driver);
        return $this;
    }

    /**
     * @return Collection|Tender[]
     */
    public function getTenders(): Collection
    {
        return $this->tenders;
    }
    public function addTender(Tender $tender): self
    {
        if (!$this->tenders->contains($tender)) {
            $this->tenders[] = $tender;
            $tender->setClaim($this);
        }

        return $this;
    }
    public function removeTender(Tender $tender): self
    {
        if ($this->tenders->removeElement($tender)) {
            // set the owning side to null (unless already changed)
            if ($tender->getClaim() === $this) {
                $tender->setClaim(null);
            }
        }
        return $this;
    }
    //
    public function getJourneyTime(): ?\DateTimeInterface
    {
        return $this->journey_time;
    }
    public function setJourneyTime(\DateTimeInterface $journey_time): self
    {
        $this->journey_time = $journey_time;
        return $this;
    }
    //
    public function getRemarkableplaceFrom(): ?Remarkableplace
    {
        return $this->remarkableplace_from;
    }
    public function setRemarkableplaceFrom(?Remarkableplace $remarkableplace_from): self
    {
        $this->remarkableplace_from = $remarkableplace_from;
        return $this;
    }
    //
    public function getRemarkableplaceTo(): ?Remarkableplace
    {
        return $this->remarkableplace_to;
    }
    public function setRemarkableplaceTo(?Remarkableplace $remarkableplace_to): self
    {
        $this->remarkableplace_to = $remarkableplace_to;
        return $this;
    }

    public function getFlatrate(): ?Flatrate
    {
        return $this->flatrate;
    }

    public function setFlatrate(?Flatrate $flatrate): self
    {
        $this->flatrate = $flatrate;

        return $this;
    }

    /**
     * @return Collection|ClaimStatus[]
     */
    public function getClaimStatuses(): Collection
    {
        return $this->claimStatuses;
    }

    public function addClaimStatus(ClaimStatus $claimStatus): self
    {
        if (!$this->claimStatuses->contains($claimStatus)) {
            $this->claimStatuses[] = $claimStatus;
            $claimStatus->setClaim($this);
        }

        return $this;
    }

    public function removeClaimStatus(ClaimStatus $claimStatus): self
    {
        if ($this->claimStatuses->removeElement($claimStatus)) {
            // set the owning side to null (unless already changed)
            if ($claimStatus->getClaim() === $this) {
                $claimStatus->setClaim(null);
            }
        }

        return $this;
    }

}
