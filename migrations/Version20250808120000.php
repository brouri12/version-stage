<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250808120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add produit_color and produit_image tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE produit_color (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, name VARCHAR(100) NOT NULL, hex_code VARCHAR(7) DEFAULT NULL, INDEX IDX_F9E0E4C8F347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit_image (id INT AUTO_INCREMENT NOT NULL, produit_id INT NOT NULL, color_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, cloudinary_public_id VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, is_main TINYINT(1) NOT NULL, INDEX IDX_4F17EDEF347EFB (produit_id), INDEX IDX_4F17EDE7ADA1FB5 (color_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE produit_color ADD CONSTRAINT FK_F9E0E4C8F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE produit_image ADD CONSTRAINT FK_4F17EDEF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE produit_image ADD CONSTRAINT FK_4F17EDE7ADA1FB5 FOREIGN KEY (color_id) REFERENCES produit_color (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE produit_image DROP FOREIGN KEY FK_4F17EDE7ADA1FB5');
        $this->addSql('ALTER TABLE produit_image DROP FOREIGN KEY FK_4F17EDEF347EFB');
        $this->addSql('ALTER TABLE produit_color DROP FOREIGN KEY FK_F9E0E4C8F347EFB');
        $this->addSql('DROP TABLE produit_image');
        $this->addSql('DROP TABLE produit_color');
    }
}

