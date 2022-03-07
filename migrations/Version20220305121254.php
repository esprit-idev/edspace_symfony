<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220305121254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie_emploi (id INT AUTO_INCREMENT NOT NULL, category_name VARCHAR(55) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE categorie_news ADD category_name VARCHAR(255) DEFAULT NULL, DROP cat_name');
        $this->addSql('ALTER TABLE emploi ADD category_name_id INT DEFAULT NULL, ADD image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emploi ADD CONSTRAINT FK_74A0B0FAB6CFDCA8 FOREIGN KEY (category_name_id) REFERENCES categorie_emploi (id)');
        $this->addSql('ALTER TABLE emploi ADD CONSTRAINT FK_74A0B0FA3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('CREATE INDEX IDX_74A0B0FAB6CFDCA8 ON emploi (category_name_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_74A0B0FA3DA5256D ON emploi (image_id)');
        $this->addSql('ALTER TABLE publication_news DROP FOREIGN KEY FK_162B189DEA27F702');
        $this->addSql('DROP INDEX IDX_162B189DEA27F702 ON publication_news');
        $this->addSql('ALTER TABLE publication_news ADD category_name_id INT DEFAULT NULL, ADD image_id INT DEFAULT NULL, ADD title VARCHAR(55) NOT NULL, ADD likes INT DEFAULT 0, ADD comments VARCHAR(6500) DEFAULT NULL, ADD vues INT DEFAULT 0, DROP categorie_news_id, CHANGE content content VARCHAR(6500) DEFAULT NULL, CHANGE owner owner VARCHAR(55) DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_news ADD CONSTRAINT FK_162B189DB6CFDCA8 FOREIGN KEY (category_name_id) REFERENCES categorie_news (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE publication_news ADD CONSTRAINT FK_162B189D3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('CREATE INDEX IDX_162B189DB6CFDCA8 ON publication_news (category_name_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_162B189D3DA5256D ON publication_news (image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emploi DROP FOREIGN KEY FK_74A0B0FAB6CFDCA8');
        $this->addSql('ALTER TABLE emploi DROP FOREIGN KEY FK_74A0B0FA3DA5256D');
        $this->addSql('ALTER TABLE publication_news DROP FOREIGN KEY FK_162B189D3DA5256D');
        $this->addSql('DROP TABLE categorie_emploi');
        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE categorie_news ADD cat_name VARCHAR(255) NOT NULL, DROP category_name');
        $this->addSql('DROP INDEX IDX_74A0B0FAB6CFDCA8 ON emploi');
        $this->addSql('DROP INDEX UNIQ_74A0B0FA3DA5256D ON emploi');
        $this->addSql('ALTER TABLE emploi DROP category_name_id, DROP image_id');
        $this->addSql('ALTER TABLE publication_news DROP FOREIGN KEY FK_162B189DB6CFDCA8');
        $this->addSql('DROP INDEX IDX_162B189DB6CFDCA8 ON publication_news');
        $this->addSql('DROP INDEX UNIQ_162B189D3DA5256D ON publication_news');
        $this->addSql('ALTER TABLE publication_news ADD categorie_news_id INT NOT NULL, DROP category_name_id, DROP image_id, DROP title, DROP likes, DROP comments, DROP vues, CHANGE owner owner VARCHAR(255) NOT NULL, CHANGE content content VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE publication_news ADD CONSTRAINT FK_162B189DEA27F702 FOREIGN KEY (categorie_news_id) REFERENCES categorie_news (id)');
        $this->addSql('CREATE INDEX IDX_162B189DEA27F702 ON publication_news (categorie_news_id)');
    }
}
