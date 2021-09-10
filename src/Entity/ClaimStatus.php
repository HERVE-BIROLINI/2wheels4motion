<?php

namespace App\Entity;

use App\Repository\ClaimStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClaimStatusRepository::class)
 */
class ClaimStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Claim::class, inversedBy="claimStatuses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $claim;

    /**
     * @ORM\ManyToOne(targetEntity=Driver::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $driver;

    // /**
    //  * @ORM\ManyToOne(targetEntity=Status::class)
    //  * @ORM\JoinColumn(nullable=false)
    //  */
    // private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Tender::class)
     */
    private $tender;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isread;

    // /**
    //  * @ORM\Column(type="boolean", nullable=true)
    //  */
    // private $istendersent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isarchived;

    public function getId(): ?int
    {
        return $this->id;
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
    public function getTender(): ?Tender
    {
        return $this->tender;
    }
    public function setTender(?Tender $tender): self
    {
        $this->tender = $tender;
        return $this;
    }

    //
    public function getIsread(): ?bool
    {
        return $this->isread;
    }
    public function setIsread(?bool $isread): self
    {
        $this->isread = $isread;
        return $this;
    }

    //
    public function getIsarchived(): ?bool
    {
        return $this->isarchived;
    }
    public function setIsarchived(?bool $isarchived): self
    {
        $this->isarchived = $isarchived;
        return $this;
    }
}
