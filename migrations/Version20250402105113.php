<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402105113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Custom migration: Add pgvector extension';
    }

    public function up(Schema $schema): void
    {
        # Source: https://github.com/pgvector/pgvector-php?tab=readme-ov-file#doctrine
        $this->addSql('CREATE EXTENSION IF NOT EXISTS vector');
    }

    public function down(Schema $schema): void
    {
    }
}
