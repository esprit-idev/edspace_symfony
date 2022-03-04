<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220219014625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie_club (id INT AUTO_INCREMENT NOT NULL, categorie_nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_news (id INT AUTO_INCREMENT NOT NULL, cat_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, niveau_id VARCHAR(255) NOT NULL, classe VARCHAR(255) NOT NULL, INDEX IDX_8F87BF96B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, club_categorie_id INT NOT NULL, club_responsable_id INT DEFAULT NULL, club_nom VARCHAR(255) NOT NULL, club_description VARCHAR(1000) DEFAULT NULL, INDEX IDX_B8EE3872C6414FDD (club_categorie_id), UNIQUE INDEX UNIQ_B8EE38729F742DA1 (club_responsable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club_pub (id INT AUTO_INCREMENT NOT NULL, club_id INT NOT NULL, pub_date DATE NOT NULL, pub_description VARCHAR(1000) DEFAULT NULL, pub_file LONGBLOB DEFAULT NULL, INDEX IDX_F9261C9C61190A32 (club_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, matiere_id VARCHAR(255) NOT NULL, niveau_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, date_insert VARCHAR(255) NOT NULL, proprietaire VARCHAR(255) NOT NULL, fichier LONGBLOB NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_D8698A76F46CD258 (matiere_id), INDEX IDX_D8698A76B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emploi (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matiere (id VARCHAR(255) NOT NULL, niveau_id VARCHAR(255) NOT NULL, INDEX IDX_9014574AB3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, classe_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, post_date DATE NOT NULL, INDEX IDX_B6BD307F8F5EA509 (classe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE niveau (id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publication_news (id INT AUTO_INCREMENT NOT NULL, categorie_news_id INT NOT NULL, content VARCHAR(255) DEFAULT NULL, owner VARCHAR(255) NOT NULL, date DATE NOT NULL, INDEX IDX_162B189DEA27F702 (categorie_news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, thread_id INT NOT NULL, reply VARCHAR(255) NOT NULL, reply_date DATETIME NOT NULL, display TINYINT(1) NOT NULL, INDEX IDX_5FB6DEC7E2904019 (thread_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thread (id INT AUTO_INCREMENT NOT NULL, thread_type_id INT NOT NULL, user_id INT NOT NULL, question VARCHAR(255) NOT NULL, nb_likes INT DEFAULT NULL, post_date DATETIME NOT NULL, display TINYINT(1) NOT NULL, INDEX IDX_31204C8374F664DA (thread_type_id), INDEX IDX_31204C83A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE thread_type (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, name VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_banned TINYINT(1) DEFAULT NULL, ban_duration INT DEFAULT NULL, roles VARCHAR(255) NOT NULL, INDEX IDX_8D93D6498F5EA509 (classe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classe ADD CONSTRAINT FK_8F87BF96B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE3872C6414FDD FOREIGN KEY (club_categorie_id) REFERENCES categorie_club (id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE38729F742DA1 FOREIGN KEY (club_responsable_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE club_pub ADD CONSTRAINT FK_F9261C9C61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76F46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574AB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE publication_news ADD CONSTRAINT FK_162B189DEA27F702 FOREIGN KEY (categorie_news_id) REFERENCES categorie_news (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7E2904019 FOREIGN KEY (thread_id) REFERENCES thread (id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C8374F664DA FOREIGN KEY (thread_type_id) REFERENCES thread_type (id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6498F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872C6414FDD');
        $this->addSql('ALTER TABLE publication_news DROP FOREIGN KEY FK_162B189DEA27F702');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F8F5EA509');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6498F5EA509');
        $this->addSql('ALTER TABLE club_pub DROP FOREIGN KEY FK_F9261C9C61190A32');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76F46CD258');
        $this->addSql('ALTER TABLE classe DROP FOREIGN KEY FK_8F87BF96B3E9C81');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76B3E9C81');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574AB3E9C81');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7E2904019');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C8374F664DA');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE38729F742DA1');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83A76ED395');
        $this->addSql('DROP TABLE categorie_club');
        $this->addSql('DROP TABLE categorie_news');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE club_pub');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE emploi');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE niveau');
        $this->addSql('DROP TABLE publication_news');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE thread');
        $this->addSql('DROP TABLE thread_type');
        $this->addSql('DROP TABLE user');
    }
}
