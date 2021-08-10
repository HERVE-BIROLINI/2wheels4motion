<?php

namespace App\Entity;

use App\Repository\SocialreasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SocialreasonRepository::class)
 */
class Socialreason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $label;

    /**
     * @ORM\ManyToMany(targetEntity=Tva::class, inversedBy="socialreasons")
     */
    private $tva;

    public function __construct()
    {
        $this->tva = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection|Tva[]
     */
    public function getTva(): Collection
    {
        return $this->tva;
    }

    public function addTva(Tva $tva): self
    {
        if (!$this->tva->contains($tva)) {
            $this->tva[] = $tva;
        }

        return $this;
    }

    public function removeTva(Tva $tva): self
    {
        $this->tva->removeElement($tva);

        return $this;
    }
}
