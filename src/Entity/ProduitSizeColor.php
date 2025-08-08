<?php

namespace App\Entity;

use App\Repository\ProduitSizeColorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitSizeColorRepository::class)]
#[ORM\Table(name: 'produit_size_color')]
#[ORM\UniqueConstraint(name: 'uniq_produit_size_color', columns: ['produit_id', 'size', 'color_id'])]
class ProduitSizeColor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private string $size;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProduitColor $color = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero]
    private int $quantite = 0;

    public function getId(): ?int { return $this->id; }

    public function getProduit(): ?Produit { return $this->produit; }
    public function setProduit(?Produit $produit): self { $this->produit = $produit; return $this; }

    public function getSize(): string { return $this->size; }
    public function setSize(string $size): self { $this->size = $size; return $this; }

    public function getColor(): ?ProduitColor { return $this->color; }
    public function setColor(?ProduitColor $color): self { $this->color = $color; return $this; }

    public function getQuantite(): int { return $this->quantite; }
    public function setQuantite(int $quantite): self { $this->quantite = $quantite; return $this; }
}

