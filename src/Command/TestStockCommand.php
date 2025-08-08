<?php

namespace App\Command;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-stock',
    description: 'Test la diminution du stock lors de la création d\'une commande',
)]
class TestStockCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PanierService $panierService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de la diminution du stock');

        // Récupérer un produit de test
        $produit = $this->entityManager->getRepository(Produit::class)->findOneBy([]);
        
        if (!$produit) {
            $io->error('Aucun produit trouvé dans la base de données');
            return Command::FAILURE;
        }

        $io->section('Informations du produit avant la commande');
        $io->text("Produit: {$produit->getNomProduit()}");
        $io->text("Stock total avant: " . ($produit->getStockTotal() ?? 'N/A'));
        
        // Afficher les tailles disponibles
        if ($produit->getProduitSizes()->count() > 0) {
            $io->text("Tailles disponibles:");
            foreach ($produit->getProduitSizes() as $size) {
                $io->text("  - {$size->getSize()}: {$size->getQuantite()} unités");
            }
        }

        // Créer un panier de test (sans client pour éviter les contraintes)
        $panierItem = new Panier();
        $panierItem->setProduit($produit);
        $panierItem->setQuantite(2);
        
        // Si le produit a des tailles, utiliser la première taille disponible
        if ($produit->getProduitSizes()->count() > 0) {
            $firstSize = $produit->getProduitSizes()->first();
            $panierItem->setTaille($firstSize->getSize());
            $io->text("Taille sélectionnée: {$firstSize->getSize()}");
        }

        $panierItems = [$panierItem];

        $io->section('Test de la mise à jour des stocks');

        try {
            // Sauvegarder l'état initial
            $stockInitial = $produit->getStockTotal();
            $taillesInitiales = [];
            
            if ($produit->getProduitSizes()->count() > 0) {
                foreach ($produit->getProduitSizes() as $size) {
                    $taillesInitiales[$size->getSize()] = $size->getQuantite();
                }
            }

            // Mettre à jour les stocks
            $io->text('Mise à jour des stocks...');
            $this->panierService->mettreAJourStocks($panierItems);

            $this->entityManager->flush();

            $io->section('Informations du produit après la mise à jour des stocks');
            
            // Recharger le produit depuis la base de données
            $this->entityManager->refresh($produit);
            
            $io->text("Stock total après: " . ($produit->getStockTotal() ?? 'N/A'));
            
            // Afficher les tailles après la commande
            if ($produit->getProduitSizes()->count() > 0) {
                $io->text("Tailles disponibles après:");
                foreach ($produit->getProduitSizes() as $size) {
                    $io->text("  - {$size->getSize()}: {$size->getQuantite()} unités");
                }
            }

            // Vérifier si le stock a diminué
            $stockApres = $produit->getStockTotal();
            
            if ($stockApres < $stockInitial) {
                $io->success("✅ Le stock a bien diminué de " . ($stockInitial - $stockApres) . " unités");
                $io->text("Stock avant: {$stockInitial}");
                $io->text("Stock après: {$stockApres}");
                $io->text("Différence: " . ($stockInitial - $stockApres) . " unités");
            } else {
                $io->error("❌ Le stock n'a pas diminué correctement");
                $io->text("Stock avant: {$stockInitial}");
                $io->text("Stock après: {$stockApres}");
            }

            // Vérifier les tailles spécifiques si applicable
            if ($panierItem->getTaille() && isset($taillesInitiales[$panierItem->getTaille()])) {
                $tailleInitiale = $taillesInitiales[$panierItem->getTaille()];
                $tailleApres = 0;
                
                foreach ($produit->getProduitSizes() as $size) {
                    if ($size->getSize() === $panierItem->getTaille()) {
                        $tailleApres = $size->getQuantite();
                        break;
                    }
                }
                
                if ($tailleApres < $tailleInitiale) {
                    $io->success("✅ La taille {$panierItem->getTaille()} a bien diminué de " . ($tailleInitiale - $tailleApres) . " unités");
                    $io->text("Taille {$panierItem->getTaille()} avant: {$tailleInitiale}");
                    $io->text("Taille {$panierItem->getTaille()} après: {$tailleApres}");
                } else {
                    $io->error("❌ La taille {$panierItem->getTaille()} n'a pas diminué correctement");
                }
            }

            // Restaurer les stocks pour ne pas affecter les données
            $io->section('Restauration des stocks');
            
            // Restaurer manuellement les stocks
            foreach ($panierItems as $item) {
                $produit = $item->getProduit();
                $quantite = $item->getQuantite();
                $taille = $item->getTaille();

                if ($taille) {
                    // Restaurer le stock pour la taille spécifique
                    foreach ($produit->getProduitSizes() as $size) {
                        if ($size->getSize() === $taille) {
                            $nouvelleQuantite = $size->getQuantite() + $quantite;
                            $size->setQuantite($nouvelleQuantite);
                            break;
                        }
                    }
                } else {
                    // Restaurer le stock total
                    $stockTotal = $produit->getStockTotal();
                    $nouveauStock = $stockTotal + $quantite;
                    $produit->setStockTotal($nouveauStock);
                }
            }
            
            $this->entityManager->flush();
            
            $io->success('Stocks restaurés à leur état initial');

        } catch (\Exception $e) {
            $io->error('Erreur lors du test: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
} 