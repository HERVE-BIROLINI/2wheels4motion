<?php

namespace App\Entity;

use App\Repository\DriverRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    // /**
    //  * @ORM\Column(type="date")
    //  */
    // private $registration_date;

    /**
     * @ORM\Column(type="date")
     */
    private $vmdtr_validity;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private $vmdtr_number;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $subscription_validity;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $motomodel;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasconfirmedgoodstanding;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="drivers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="driver", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity=Claim::class, mappedBy="drivers")
     */
    private $claims;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_verified;

    /**
     * @ORM\ManyToMany(targetEntity=Tender::class, inversedBy="drivers")
     */
    private $tenders;
    
    // * @ORM\ManyToMany(targetEntity=Claim::class, mappedBy="claim")

    public function __construct()
    {
        $this->claims=new ArrayCollection();
        $this->tenders=new ArrayCollection();
    }

    //
    public function getId(): ?int
    {
        return $this->id;
    }
    //
    public function getVmdtrValidity(): ?\DateTimeInterface
    {
        return $this->vmdtr_validity;
    }
    public function setVmdtrValidity(\DateTimeInterface $vmdtr_validity): self
    {
        $this->vmdtr_validity=$vmdtr_validity;
        return $this;
    }
    //
    public function getVmdtrNumber(): ?string
    {
        return $this->vmdtr_number;
    }
    public function setVmdtrNumber(string $vmdtr_number): self
    {
        $this->vmdtr_number=$vmdtr_number;
        return $this;
    }
    //
    public function getSubscriptionValidity(): ?\DateTimeInterface
    {
        return $this->subscription_validity;
    }
    public function setSubscriptionValidity(?\DateTimeInterface $subscription_validity): self
    {
        $this->subscription_validity=$subscription_validity;
        return $this;
    }
    //
    public function getMotomodel(): ?string
    {
        return $this->motomodel;
    }
    public function setMotomodel(string $motomodel): self
    {
        $this->motomodel=$motomodel;
        return $this;
    }
    //
    public function getHasconfirmedgoodstanding(): ?bool
    {
        return $this->hasconfirmedgoodstanding;
    }
    public function setHasconfirmedgoodstanding(bool $hasconfirmedgoodstanding): self
    {
        $this->hasconfirmedgoodstanding=$hasconfirmedgoodstanding;
        return $this;
    }
    //
    public function getCompany(): ?Company
    {
        return $this->company;
    }
    public function setCompany(?Company $company): self
    {
        $this->company=$company;
        return $this;
    }
    //
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setDriver(null);
        }
        // set the owning side of the relation if necessary
        if ($user !== null && $user->getDriver() !== $this) {
            $user->setDriver($this);
        }
        $this->user=$user;
        return $this;
    }

    /**
     * @return Collection|Claim[]
     */
    public function getClaims(): Collection
    {
        return $this->claims;
    }
    public function addClaim(Claim $claim): self
    {
        if (!$this->claims->contains($claim)) {
            $this->claims[]=$claim;
            $claim->addDriver($this);
        }
        return $this;
    }
    public function removeClaim(Claim $claim): self
    {
        if ($this->claims->removeElement($claim)) {
            $claim->removeDriver($this);
        }
        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(?bool $is_verified): self
    {
        $this->is_verified=$is_verified;

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
            $this->tenders[]=$tender;
        }

        return $this;
    }

    public function removeTender(Tender $tender): self
    {
        $this->tenders->removeElement($tender);

        return $this;
    }

}
