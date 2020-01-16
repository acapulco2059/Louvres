<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200115134058 extends AbstractMigration
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
        $this->addSql('ALTER TABLE country CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE name_en_gb name_en_gb VARCHAR(45) NOT NULL, CHANGE name_fr_fr name_fr_fr VARCHAR(45) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE country CHANGE id id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE name_en_gb name_en_gb VARCHAR(45) CHARACTER SET utf8 DEFAULT \'\' NOT NULL COLLATE `utf8_general_ci`, CHANGE name_fr_fr name_fr_fr VARCHAR(45) CHARACTER SET utf8 DEFAULT \'\' NOT NULL COLLATE `utf8_general_ci`');
        $this->addSql('CREATE UNIQUE INDEX alpha3 ON country (alpha3)');
        $this->addSql('CREATE UNIQUE INDEX alpha2 ON country (alpha2)');
        $this->addSql('CREATE UNIQUE INDEX code_unique ON country (code)');
    }
}
