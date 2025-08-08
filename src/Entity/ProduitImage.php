<?php

namespace App\Entity;

use App\Repository\ProduitImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitImageRepository::class)]
class ProduitImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?ProduitColor $color = null;

    #[ORM\Column(length: 255)]
    private string $url;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cloudinaryPublicId = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isMain = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    public function getColor(): ?ProduitColor
    {
        return $this->color;
    }

    public function setColor(?ProduitColor $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getCloudinaryPublicId(): ?string
    {
        return $this->cloudinaryPublicId;
    }

    public function setCloudinaryPublicId(?string $cloudinaryPublicId): self
    {
        $this->cloudinaryPublicId = $cloudinaryPublicId;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;
        return $this;
    }
}

