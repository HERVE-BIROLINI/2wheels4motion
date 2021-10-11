<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BookingRepository::class)
 * @ORM\Table(name="`booking`")
 */
class Booking
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

/*
    / * *
     * @ORM\Column(type="datetime")
     * /
    private $evaluation_datetime;
*/

    /**
     * @ORM\OneToOne(targetEntity=Tender::class, inversedBy="booking", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $tender;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $wasexecuted;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isarchivedbydriver;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isarchivedbycustomer;

    /**
     * @ORM\ManyToOne(targetEntity=Paymentlabel::class, inversedBy="bookings")
     */
    private $paidby;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $paid_date;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $paidby_label;

    public function getId(): ?int
    {
        return $this->id;
    }

/*
    public function getEvaluationDatetime(): ?\DateTimeInterface
    {
        return $this->evaluation_datetime;
    }
    public function setEvaluationDatetime(\DateTimeInterface $evaluation_datetime): self
    {
        $this->evaluation_datetime = $evaluation_datetime;
        return $this;
    }
*/

    public function getTender(): ?Tender
    {
        return $this->tender;
    }
    public function setTender(Tender $tender): self
    {
        $this->tender = $tender;
        return $this;
    }

    //
    public function getWasexecuted(): ?bool
    {
        return $this->wasexecuted;
    }
    public function setWasexecuted(?bool $wasexecuted): self
    {
        $this->wasexecuted = $wasexecuted;
        return $this;
    }

    //
    public function getIsarchivedbydriver(): ?bool
    {
        return $this->isarchivedbydriver;
    }
    public function setIsarchivedbydriver(?bool $isarchivedbydriver): self
    {
        $this->isarchivedbydriver = $isarchivedbydriver;
        return $this;
    }

    //
    public function getIsarchivedbycustomer(): ?bool
    {
        return $this->isarchivedbycustomer;
    }
    public function setIsarchivedbycustomer(?bool $isarchivedbycustomer): self
    {
        $this->isarchivedbycustomer = $isarchivedbycustomer;
        return $this;
    }

    public function getPaidby(): ?Paymentlabel
    {
        return $this->paidby;
    }

    public function setPaidby(?Paymentlabel $paidby): self
    {
        $this->paidby = $paidby;

        return $this;
    }

    public function getPaidDate(): ?\DateTimeInterface
    {
        return $this->paid_date;
    }

    public function setPaidDate(?\DateTimeInterface $paid_date): self
    {
        $this->paid_date = $paid_date;

        return $this;
    }

    public function getPaidbyLabel(): ?string
    {
        return $this->paidby_label;
    }

    public function setPaidbyLabel(?string $paidby_label): self
    {
        $this->paidby_label = $paidby_label;

        return $this;
    }
}
