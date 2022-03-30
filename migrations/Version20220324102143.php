<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220324102143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emploi DROP FOREIGN KEY FK_74A0B0FA3DA5256D');
        $this->addSql('ALTER TABLE publication_news DROP FOREIGN KEY FK_162B189D3DA5256D');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP INDEX UNIQ_74A0B0FA3DA5256D ON emploi');
        $this->addSql('ALTER TABLE emploi ADD image VARCHAR(255) DEFAULT NULL, DROP image_id');
        $this->addSql('DROP INDEX UNIQ_162B189D3DA5256D ON publication_news');
        $this->addSql('ALTER TABLE publication_news ADD image VARCHAR(255) DEFAULT NULL, DROP image_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE emploi ADD image_id INT DEFAULT NULL, DROP image');
        $this->addSql('ALTER TABLE emploi ADD CONSTRAINT FK_74A0B0FA3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_74A0B0FA3DA5256D ON emploi (image_id)');
        $this->addSql('ALTER TABLE publication_news ADD image_id INT DEFAULT NULL, DROP image');
        $this->addSql('ALTER TABLE publication_news ADD CONSTRAINT FK_162B189D3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_162B189D3DA5256D ON publication_news (image_id)');
    }
}
