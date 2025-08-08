<?php

namespace App\Entity;

use App\Repository\ProduitColorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitColorRepository::class)]
class ProduitColor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'colors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom de la couleur est requis')]
    private ?string $name = null;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $hexCode = null;

    /**
     * @var Collection<int, ProduitImage>
     */
    #[ORM\OneToMany(mappedBy: 'color', targetEntity: ProduitImage::class, cascade: ['persist'], orphanRemoval: false)]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getHexCode(): ?string
    {
        return $this->hexCode;
    }

    public function setHexCode(?string $hexCode): self
    {
        $this->hexCode = $hexCode;
        return $this;
    }

    /**
     * @return Collection<int, ProduitImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProduitImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setColor($this);
        }
        return $this;
    }

    public function removeImage(ProduitImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getColor() === $this) {
                $image->setColor(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}

