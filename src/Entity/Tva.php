<?php

namespace App\Entity;

use App\Repository\TvaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TvaRepository::class)
 */
class Tva
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comment;

    /**
     * @ORM\ManyToMany(targetEntity=Socialreason::class, mappedBy="tva")
     */
    private $socialreasons;

    public function __construct()
    {
        $this->socialreasons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return Collection|Socialreason[]
     */
    public function getSocialreasons(): Collection
    {
        return $this->socialreasons;
    }

    public function addSocialreason(Socialreason $socialreason): self
    {
        if (!$this->socialreasons->contains($socialreason)) {
            $this->socialreasons[] = $socialreason;
            $socialreason->addTva($this);
        }
        return $this;
    }

    public function removeSocialreason(Socialreason $socialreason): self
    {
        if ($this->socialreasons->removeElement($socialreason)) {
            $socialreason->removeTva($this);
        }
        return $this;
    }
}
