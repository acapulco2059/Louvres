<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191126180312 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, code INT NOT NULL, alpha2 VARCHAR(255) NOT NULL, alpha3 VARCHAR(255) NOT NULL, name_en_gb VARCHAR(255) NOT NULL, name_fr_fr VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ordered (id INT AUTO_INCREMENT NOT NULL, unique_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) NOT NULL, total_price NUMERIC(7, 2) NOT NULL, number_of_ticket INT NOT NULL, visit_day DATE NOT NULL, state SMALLINT NOT NULL, half_day TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_C3121F99E3C68343 (unique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, ordered_id INT DEFAULT NULL, unique_id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', price NUMERIC(7, 2) NOT NULL, UNIQUE INDEX UNIQ_97A0ADA3E3C68343 (unique_id), INDEX IDX_97A0ADA3AA60395A (ordered_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, country_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, UNIQUE INDEX UNIQ_8D93D649F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3AA60395A FOREIGN KEY (ordered_id) REFERENCES ordered (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F92F3E70');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3AA60395A');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE ordered');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE user');
    }
}
