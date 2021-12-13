<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211208075451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE expense (id INT AUTO_INCREMENT NOT NULL, gas_station_id INT NOT NULL, vehicle_id INT NOT NULL, expense_number VARCHAR(64) NOT NULL, invoice_number VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, issued_on DATETIME NOT NULL, category VARCHAR(255) NOT NULL, value_ti DOUBLE PRECISION NOT NULL, tax_rate DOUBLE PRECISION NOT NULL, value_te DOUBLE PRECISION NOT NULL, INDEX IDX_2D3A8DA6916BFF50 (gas_station_id), INDEX IDX_2D3A8DA6545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gas_station (id INT AUTO_INCREMENT NOT NULL, coordinate VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, plate_number VARCHAR(20) NOT NULL, brand VARCHAR(100) NOT NULL, modal VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6916BFF50 FOREIGN KEY (gas_station_id) REFERENCES gas_station (id)');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_2D3A8DA6545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA6916BFF50');
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_2D3A8DA6545317D1');
        $this->addSql('DROP TABLE expense');
        $this->addSql('DROP TABLE gas_station');
        $this->addSql('DROP TABLE vehicle');
    }
}
