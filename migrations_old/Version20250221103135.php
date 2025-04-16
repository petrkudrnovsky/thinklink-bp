<?php

declare(strict_types=1);

namespace migrations_old;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250221103135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pgvector extension';
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
