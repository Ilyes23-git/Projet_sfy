<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateModification = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: PanierProduit::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->dateModification = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTime
    {
        return $this->dateModification;
    }

    public function setDateModification(\DateTime $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Collection<int, PanierProduit>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Add a product to the panier
     */
    public function addProduit(Produit $produit, int $quantite = 1): static
    {
        foreach ($this->items as $item) {
            if ($item->getProduit() === $produit) {
                $item->setQuantite($item->getQuantite() + $quantite);
                return $this;
            }
        }

        $item = new PanierProduit();
        $item->setPanier($this)
             ->setProduit($produit)
             ->setQuantite($quantite)
             ->setPrixUnitaire($produit->getPrix());

        $this->items->add($item);

        return $this;
    }

    /**
     * Remove a product from the panier
     */
    public function removeProduit(Produit $produit): static
    {
        foreach ($this->items as $item) {
            if ($item->getProduit() === $produit) {
                $this->items->removeElement($item);
                break;
            }
        }

        return $this;
    }

    /**
     * Get the total price of the panier
     */
    public function getPrixTotal(): float
    {
        $total = 0;

        foreach ($this->items as $item) {
            $total += $item->getPrixUnitaire() * $item->getQuantite();
        }

        return $total;
    }
}
