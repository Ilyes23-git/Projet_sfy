<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?float $pourcentage = null;

    #[ORM\Column]
    private ?bool $estActive = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'promotions')]
    private Collection $duréePromotion;

    public function __construct()
    {
        $this->duréePromotion = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPourcentage(): ?float
    {
        return $this->pourcentage;
    }

    public function setPourcentage(float $pourcentage): static
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    public function isEstActive(): ?bool
    {
        return $this->estActive;
    }

    public function setEstActive(bool $estActive): static
    {
        $this->estActive = $estActive;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getDuréePromotion(): Collection
    {
        return $this->duréePromotion;
    }

    public function addDurEPromotion(Produit $durEPromotion): static
    {
        if (!$this->duréePromotion->contains($durEPromotion)) {
            $this->duréePromotion->add($durEPromotion);
        }

        return $this;
    }

    public function removeDurEPromotion(Produit $durEPromotion): static
    {
        $this->duréePromotion->removeElement($durEPromotion);

        return $this;
    }
}
