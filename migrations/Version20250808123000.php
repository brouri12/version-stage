<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250808123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add produit_size_color table to track quantity per size and color for clothing';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE produit_size_color (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, color_id INT NOT NULL, size VARCHAR(50) NOT NULL, quantite INT NOT NULL, INDEX IDX_PSC_PRODUIT (produit_id), INDEX IDX_PSC_COLOR (color_id), UNIQUE INDEX uniq_produit_size_color (produit_id, size, color_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produit_size_color ADD CONSTRAINT FK_PSC_PRODUIT FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE produit_size_color ADD CONSTRAINT FK_PSC_COLOR FOREIGN KEY (color_id) REFERENCES produit_color (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE produit_size_color');
    }
}

