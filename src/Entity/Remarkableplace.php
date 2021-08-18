<?php

namespace App\Entity;

use App\Repository\RemarkableplaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RemarkableplaceRepository::class)
 */
class Remarkableplace
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
    private $label;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $dept_code;

    /**
     * @ORM\ManyToOne(targetEntity=Typeplace::class, inversedBy="remarkableplaces")
     * @ORM\JoinColumn(nullable=false)
     */
    private $typeplace;

    /**
     * @ORM\OneToMany(targetEntity=Claim::class, mappedBy="remarkableplace_from")
     */
    private $claims_from;

    /**
     * @ORM\OneToMany(targetEntity=Claim::class, mappedBy="remarkableplace_to")
     */
    private $claims_to;

    public function __construct()
    {
        $this->claims_from = new ArrayCollection();
        $this->claims_to = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    //
    public function getLabel(): ?string
    {
        return $this->label;
    }
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }
    //
    public function getDeptCode(): ?string
    {
        return $this->dept_code;
    }
    public function setDeptCode(string $dept_code): self
    {
        $this->dept_code = $dept_code;
        return $this;
    }
    //
    public function getTypeplace(): ?Typeplace
    {
        return $this->typeplace;
    }
    public function setTypeplace(?Typeplace $typeplace): self
    {
        $this->typeplace = $typeplace;
        return $this;
    }

    /**
     * @return Collection|Claim[]
     */
    public function getClaimsFrom(): Collection
    {
        return $this->claims_from;
    }
    public function addClaimsFrom(Claim $claimsFrom): self
    {
        if (!$this->claims_from->contains($claimsFrom)) {
            $this->claims_from[] = $claimsFrom;
            $claimsFrom->setRemarkableplaceFrom($this);
        }
        return $this;
    }
    public function removeClaimsFrom(Claim $claimsFrom): self
    {
        if ($this->claims_from->removeElement($claimsFrom)) {
            // set the owning side to null (unless already changed)
            if ($claimsFrom->getRemarkableplaceFrom() === $this) {
                $claimsFrom->setRemarkableplaceFrom(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Claim[]
     */
    public function getClaimsTo(): Collection
    {
        return $this->claims_to;
    }
    public function addClaimsTo(Claim $claimsTo): self
    {
        if (!$this->claims_to->contains($claimsTo)) {
            $this->claims_to[] = $claimsTo;
            $claimsTo->setRemarkableplaceTo($this);
        }
        return $this;
    }
    public function removeClaimsTo(Claim $claimsTo): self
    {
        if ($this->claims_to->removeElement($claimsTo)) {
            // set the owning side to null (unless already changed)
            if ($claimsTo->getRemarkableplaceTo() === $this) {
                $claimsTo->setRemarkableplaceTo(null);
            }
        }
        return $this;
    }
}
