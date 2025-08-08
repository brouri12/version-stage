<?php

namespace App\Service;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\ProduitSizeColor;
use App\Repository\FraisLivraisonRepository;
use App\Repository\TaxeRepository;
use Doctrine\ORM\EntityManagerInterface;

class PanierService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FraisLivraisonRepository $fraisLivraisonRepository,
        private TaxeRepository $taxeRepository
    ) {
    }

    /**
     * Calcule le total du panier avec frais de livraison et taxes
     */
    public function calculerTotalPanier(array $panierItems): array
    {
        $sousTotal = 0;
        
        // Calculer le sous-total
        foreach ($panierItems as $item) {
            $sousTotal += $item->getQuantite() * $item->getProduit()->getPrixUnitaire();
        }

        // Récupérer les frais de livraison
        $fraisLivraison = $this->fraisLivraisonRepository->findActifs();
        $fraisLivraisonMontant = 0;
        if (!empty($fraisLivraison)) {
            $fraisLivraisonMontant = $fraisLivraison[0]->getMontant();
        }

        // Récupérer et calculer les taxes
        $taxes = $this->taxeRepository->findActives();
        $totalTaxes = 0;
        $detailsTaxes = [];

        foreach ($taxes as $taxe) {
            $montantTaxe = $sousTotal * $taxe->getTaux() / 100;
            $totalTaxes += $montantTaxe;
            $detailsTaxes[] = [
                'nom' => $taxe->getNom(),
                'taux' => $taxe->getTaux(),
                'montant' => $montantTaxe
            ];
        }

        // Total final sans taxes (taxes supprimées du résumé)
        $totalFinal = $sousTotal + $fraisLivraisonMontant;

        return [
            'sous_total' => $sousTotal,
            'frais_livraison' => $fraisLivraison,
            'frais_livraison_montant' => $fraisLivraisonMontant,
            'taxes' => $taxes,
            'details_taxes' => $detailsTaxes,
            'total_taxes' => $totalTaxes,
            'total_final' => $totalFinal
        ];
    }

    /**
     * Vérifie la disponibilité des produits dans le panier
     */
    public function verifierDisponibilite(array $panierItems): array
    {
        $erreurs = [];
        
        foreach ($panierItems as $item) {
            $produit = $item->getProduit();
            $quantiteDemandee = $item->getQuantite();
            $taille = $item->getTaille();

            if ($taille) {
                // Pour les vêtements: somme des quantités par couleur (table ProduitSizeColor)
                if ($this->isProduitVetement($produit)) {
                    $repo = $this->entityManager->getRepository(ProduitSizeColor::class);
                    $entries = $repo->findBy(['produit' => $produit, 'size' => $taille]);
                    $total = 0;
                    foreach ($entries as $e) { $total += max(0, (int)$e->getQuantite()); }
                    if ($total < $quantiteDemandee) {
                        $erreurs[] = "Le produit {$produit->getNomProduit()} n'est pas disponible en quantité suffisante pour la taille $taille";
                    }
                } else {
                    // Vérifier la disponibilité pour la taille spécifique (ancienne logique)
                    $produitSize = null;
                    foreach ($produit->getProduitSizes() as $size) {
                        if ($size->getSize() === $taille) { $produitSize = $size; break; }
                    }
                    if (!$produitSize || $produitSize->getQuantite() < $quantiteDemandee) {
                        $erreurs[] = "Le produit {$produit->getNomProduit()} n'est pas disponible en quantité suffisante pour la taille $taille";
                    }
                }
            } else {
                // Vérifier la disponibilité globale
                $stockTotal = $produit->getTotalStock();
                if ($stockTotal < $quantiteDemandee) {
                    $erreurs[] = "Le produit {$produit->getNomProduit()} n'est pas disponible en quantité suffisante";
                }
            }
        }

        return $erreurs;
    }

    /**
     * Met à jour les stocks après une commande
     */
    public function mettreAJourStocks(array $panierItems): void
    {
        foreach ($panierItems as $item) {
            $produit = $item->getProduit();
            $quantite = $item->getQuantite();
            $taille = $item->getTaille();

            if ($taille) {
                if ($this->isProduitVetement($produit)) {
                    // Répartir la décrémentation sur les entrées couleur disponibles (greedy)
                    $repo = $this->entityManager->getRepository(ProduitSizeColor::class);
                    $entries = $repo->findBy(['produit' => $produit, 'size' => $taille]);
                    // Trier par quantité décroissante
                    usort($entries, fn($a,$b) => $b->getQuantite() <=> $a->getQuantite());
                    $restant = $quantite;
                    foreach ($entries as $e) {
                        if ($restant <= 0) break;
                        $take = min($e->getQuantite(), $restant);
                        $e->setQuantite(max(0, $e->getQuantite() - $take));
                        $restant -= $take;
                    }
                    // Mettre à jour ProduitSize avec la somme restante
                    $this->syncProduitSizeFromPSC($produit, $taille);
                    $produit->setStockTotal();
                } else {
                    // Mettre à jour le stock pour la taille spécifique (ancienne logique)
                    foreach ($produit->getProduitSizes() as $size) {
                        if ($size->getSize() === $taille) {
                            $ancienneQuantite = $size->getQuantite();
                            $nouvelleQuantite = $size->getQuantite() - $quantite;
                            $size->setQuantite(max(0, $nouvelleQuantite));
                            error_log("Stock mis à jour pour {$produit->getNomProduit()} - Taille: {$taille} - Ancien: {$ancienneQuantite} - Nouveau: {$size->getQuantite()} - Quantité commandée: {$quantite}");
                            break;
                        }
                    }
                    $produit->setStockTotal();
                }
            } else {
                // Mettre à jour le stock total
                $ancienStock = $produit->getStockTotal();
                $nouveauStock = $ancienStock - $quantite;
                $produit->setStockTotal(max(0, $nouveauStock));
                
                // Log pour le débogage
                error_log("Stock total mis à jour pour {$produit->getNomProduit()} - Ancien: {$ancienStock} - Nouveau: {$produit->getStockTotal()} - Quantité commandée: {$quantite}");
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Restaure les stocks après l'annulation d'une commande
     */
    public function restaurerStocks(array $lignesCommande): void
    {
        foreach ($lignesCommande as $ligne) {
            $produit = $ligne->getProduit();
            $quantite = $ligne->getQuantite();
            $taille = $ligne->getTaille();

            if ($taille) {
                if ($this->isProduitVetement($produit)) {
                    // Répartir l'incrément sur les entrées couleur (simplement sur la première entrée)
                    $repo = $this->entityManager->getRepository(ProduitSizeColor::class);
                    $entries = $repo->findBy(['produit' => $produit, 'size' => $taille]);
                    if (!empty($entries)) {
                        $entries[0]->setQuantite($entries[0]->getQuantite() + $quantite);
                    }
                    $this->syncProduitSizeFromPSC($produit, $taille);
                    $produit->setStockTotal();
                } else {
                    // Restaurer le stock pour la taille spécifique
                    foreach ($produit->getProduitSizes() as $size) {
                        if ($size->getSize() === $taille) {
                            $nouvelleQuantite = $size->getQuantite() + $quantite;
                            $size->setQuantite($nouvelleQuantite);
                            break;
                        }
                    }
                    $produit->setStockTotal();
                }
            } else {
                // Restaurer le stock total
                $stockTotal = $produit->getStockTotal();
                $nouveauStock = $stockTotal + $quantite;
                $produit->setStockTotal($nouveauStock);
            }
        }

        $this->entityManager->flush();
    }

    private function isProduitVetement(Produit $produit): bool
    {
        $cat = $produit->getCategorie() ? strtolower($produit->getCategorie()->getNomCategorie()) : '';
        $cat = str_replace(['é','è','ê','ë'], 'e', $cat);
        return (bool) preg_match('/^vetement(s)?$/', $cat);
    }

    private function syncProduitSizeFromPSC(Produit $produit, string $taille): void
    {
        $repo = $this->entityManager->getRepository(ProduitSizeColor::class);
        $entries = $repo->findBy(['produit' => $produit, 'size' => $taille]);
        $sum = 0; foreach ($entries as $e) { $sum += max(0, (int)$e->getQuantite()); }
        foreach ($produit->getProduitSizes() as $ps) {
            if ($ps->getSize() === $taille) { $ps->setQuantite($sum); break; }
        }
    }
} 