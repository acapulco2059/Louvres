<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191126213952 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX alpha3 ON country');
        $this->addSql('DROP INDEX alpha2 ON country');
        $this->addSql('DROP INDEX code_unique ON country');
        $this->addSql('ALTER TABLE country ADD name_en_gb VARCHAR(255) NOT NULL, ADD name_fr_fr VARCHAR(255) NOT NULL, DROP nom_en_gb, DROP nom_fr_fr, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE alpha2 alpha2 VARCHAR(255) NOT NULL, CHANGE alpha3 alpha3 VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country ADD nom_en_gb VARCHAR(45) NOT NULL COLLATE utf8_general_ci, ADD nom_fr_fr VARCHAR(45) NOT NULL COLLATE utf8_general_ci, DROP name_en_gb, DROP name_fr_fr, CHANGE id id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE alpha2 alpha2 VARCHAR(2) NOT NULL COLLATE utf8_general_ci, CHANGE alpha3 alpha3 VARCHAR(3) NOT NULL COLLATE utf8_general_ci');
        $this->addSql('CREATE UNIQUE INDEX alpha3 ON country (alpha3)');
        $this->addSql('CREATE UNIQUE INDEX alpha2 ON country (alpha2)');
        $this->addSql('CREATE UNIQUE INDEX code_unique ON country (code)');
    }
}
