<?php

namespace App\Entity;

use App\Repository\TenderStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TenderStatusRepository::class)
 */
class TenderStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Tender::class, inversedBy="tenderStatus", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $tender;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isread;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isarchivedbydriver;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isarchivedbycustomer;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $isacceptedbycustomer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isbookingconfirmedbydriver;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTender(): ?Tender
    {
        return $this->tender;
    }

    public function setTender(Tender $tender): self
    {
        $this->tender = $tender;

        return $this;
    }

    public function getIsread(): ?bool
    {
        return $this->isread;
    }

    public function setIsread(?bool $isread): self
    {
        $this->isread = $isread;

        return $this;
    }

    public function getIsarchivedbydriver(): ?bool
    {
        return $this->isarchivedbydriver;
    }

    public function setIsarchivedbydriver(?bool $isarchivedbydriver): self
    {
        $this->isarchivedbydriver = $isarchivedbydriver;

        return $this;
    }

    public function getIsarchivedbycustomer(): ?bool
    {
        return $this->isarchivedbycustomer;
    }

    public function setIsarchivedbycustomer(?bool $isarchivedbycustomer): self
    {
        $this->isarchivedbycustomer = $isarchivedbycustomer;

        return $this;
    }

    public function getIsacceptedbycustomer(): ?int
    {
        return $this->isacceptedbycustomer;
    }

    public function setIsacceptedbycustomer(int $isacceptedbycustomer): self
    {
        $this->isacceptedbycustomer = $isacceptedbycustomer;

        return $this;
    }

    public function getIsbookingconfirmedbydriver(): ?bool
    {
        return $this->isbookingconfirmedbydriver;
    }

    public function setIsbookingconfirmedbydriver(?bool $isbookingconfirmedbydriver): self
    {
        $this->isbookingconfirmedbydriver = $isbookingconfirmedbydriver;

        return $this;
    }
}
