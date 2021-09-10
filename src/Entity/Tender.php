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
    private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $tender_datetime;

    /**
     * @ORM\Column(type="time")
     */
    private $rdvat_time;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=1)
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity=Claim::class, inversedBy="tender")
     * @ORM\JoinColumn(nullable=false)
     */
    private $claim;

    /**
     * @ORM\ManyToOne(targetEntity=Driver::class, inversedBy="tender")
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    /**
     * @ORM\Column(type="time")
     */
    private $arrivalat_datetime;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=1)
     */
    private $distance;

    /**
     * @ORM\Column(type="decimal", precision=3, scale=1)
     */
    private $priceperkm;

    /**
     * @ORM\Column(type="integer")
     */
    private $pickupcost;

    /**
     * @ORM\ManyToOne(targetEntity=Tva::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tva;

    /**
     * @ORM\ManyToOne(targetEntity=Flatrate::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $flatrate;

    //
    public function __construct()
    {
        $this->drivers = new ArrayCollection();
    }

    //
    public function getId(): ?int
    {
        return $this->id;
    }

    //
    public function getNumber(): ?string
    {
        return $this->number;
    }
    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    //
    public function getTenderDatetime(): ?\DateTimeInterface
    {
        return $this->tender_datetime;
    }
    public function setTenderDatetime(\DateTimeInterface $tender_datetime): self
    {
        $this->tender_datetime = $tender_datetime;
        return $this;
    }

    //
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

    //
    public function setComments(string $comments): self
    {
        $this->comments = $comments;
        return $this;
    }
    public function getPrice(): ?int
    {
        return $this->price;
    }

    //
    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    //
    public function getClaim(): ?Claim
    {
        return $this->claim;
    }
    public function setClaim(?Claim $claim): self
    {
        $this->claim = $claim;
        return $this;
    }

    //
    public function getDriver(): ?Driver
    {
        return $this->driver;
    }
    public function setDriver(?Driver $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    //
    public function getArrivalatDatetime(): ?\DateTimeInterface
    {
        return $this->arrivalat_datetime;
    }
    public function setArrivalatDatetime(\DateTimeInterface $arrivalat_datetime): self
    {
        $this->arrivalat_datetime = $arrivalat_datetime;
        return $this;
    }

    //
    public function getDistance(): ?string
    {
        return $this->distance;
    }
    public function setDistance(string $distance): self
    {
        $this->distance = $distance;
        return $this;
    }

    //
    public function getPriceperkm(): ?int
    {
        return $this->priceperkm;
    }
    public function setPriceperkm(int $priceperkm): self
    {
        $this->priceperkm = $priceperkm;
        return $this;
    }

    //
    public function getPickupcost(): ?int
    {
        return $this->pickupcost;
    }
    public function setPickupcost(int $pickupcost): self
    {
        $this->pickupcost = $pickupcost;
        return $this;
    }

    //
    public function getTva(): ?Tva
    {
        return $this->tva;
    }
    public function setTva(?Tva $tva): self
    {
        $this->tva = $tva;
        return $this;
    }

    //
    public function getFlatrate(): ?Flatrate
    {
        return $this->flatrate;
    }
    public function setFlatrate(?Flatrate $flatrate): self
    {
        $this->flatrate = $flatrate;
        return $this;
    }
}
