<?php

namespace App\Entity;

use App\Repository\TypeplaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeplaceRepository::class)
 */
class Typeplace
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
     * @ORM\OneToMany(targetEntity=Remarkableplace::class, mappedBy="typeplace")
     */
    private $remarkableplaces;

    public function __construct()
    {
        $this->remarkableplaces = new ArrayCollection();
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
     * @return Collection|Remarkableplace[]
     */
    public function getRemarkableplaces(): Collection
    {
        return $this->remarkableplaces;
    }

    public function addRemarkableplace(Remarkableplace $remarkableplace): self
    {
        if (!$this->remarkableplaces->contains($remarkableplace)) {
            $this->remarkableplaces[] = $remarkableplace;
            $remarkableplace->setTypeplace($this);
        }
        return $this;
    }

    public function removeRemarkableplace(Remarkableplace $remarkableplace): self
    {
        if ($this->remarkableplaces->removeElement($remarkableplace)) {
            // set the owning side to null (unless already changed)
            if ($remarkableplace->getTypeplace() === $this) {
                $remarkableplace->setTypeplace(null);
            }
        }
        return $this;
    }
}
