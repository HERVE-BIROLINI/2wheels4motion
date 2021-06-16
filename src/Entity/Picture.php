<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PictureRepository::class)
 */
class Picture
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pathname;

    /**
     * @ORM\ManyToOne(targetEntity=Picturelabel::class, inversedBy="pictures", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $picturelabel;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="picture")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPathname(): ?string
    {
        return $this->pathname;
    }

    public function setPathname(string $pathname): self
    {
        $this->pathname = $pathname;

        return $this;
    }

    public function getPicturelabel(): ?Picturelabel
    {
        return $this->picturelabel;
    }

    public function setPicturelabel(?Picturelabel $picturelabel): self
    {
        $this->picturelabel = $picturelabel;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
