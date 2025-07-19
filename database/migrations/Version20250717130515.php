<?php

declare(strict_types=1);

namespace RenalTales\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717130515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create languages table for multilingual support';
    }

    public function up(Schema $schema): void
    {
        // Create languages table only if it doesn't exist
        $this->addSql('CREATE TABLE IF NOT EXISTS languages (
            id INT AUTO_INCREMENT NOT NULL,
            createdAt DATETIME NOT NULL,
            updatedAt DATETIME NOT NULL,
            code VARCHAR(5) NOT NULL,
            name VARCHAR(80) NOT NULL,
            nativeName VARCHAR(80) NOT NULL,
            active TINYINT(1) NOT NULL,
            isDefault TINYINT(1) NOT NULL,
            direction VARCHAR(3) DEFAULT NULL,
            region VARCHAR(5) DEFAULT NULL,
            sortOrder INT NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');

        // Add indexes separately to avoid potential issues
        $this->addSql('CREATE UNIQUE INDEX languages_code_unique ON languages (code)');
        $this->addSql('CREATE INDEX languages_active_idx ON languages (active)');
    }

    public function down(Schema $schema): void
    {
        // Drop languages table if it exists
        $this->addSql('DROP TABLE IF EXISTS languages');
    }
}
