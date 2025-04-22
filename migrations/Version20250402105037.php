<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402105037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Custom migration: Add generated tsvector to content column on the Note entity';
    }

    public function up(Schema $schema): void
    {
        # Source: https://www.postgresql.org/docs/current/textsearch-tables.html#TEXTSEARCH-TABLES-INDEX (Example with generated tsvector)
        $this->addSql("ALTER TABLE note ADD COLUMN note_tsvector tsvector GENERATED ALWAYS AS (to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(content, ''))) STORED");
        $this->addSql("CREATE INDEX idx_note_tsvector ON note USING GIN (note_tsvector)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP INDEX idx_note_tsvector");
        $this->addSql("ALTER TABLE notes DROP COLUMN note_tsvector");
    }
}
