<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220214132218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, matiere_id VARCHAR(255) NOT NULL, niveau_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, date_insert VARCHAR(255) NOT NULL, proprietaire VARCHAR(255) NOT NULL, fichier LONGBLOB NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_D8698A76F46CD258 (matiere_id), INDEX IDX_D8698A76B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matiere (id VARCHAR(255) NOT NULL, niveau_id VARCHAR(255) NOT NULL, INDEX IDX_9014574AB3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE niveau (id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574AB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76F46CD258');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76B3E9C81');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574AB3E9C81');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE niveau');
    }
}
