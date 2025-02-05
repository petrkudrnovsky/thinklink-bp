<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241227101650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug_sequence table to track slug sequences.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE slug_sequence (id SERIAL NOT NULL, slug VARCHAR(255) NOT NULL, slug_order INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_313101C989D9B62 ON slug_sequence (slug)');
        $this->addSql('ALTER TABLE note ALTER slug TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE slug_sequence');
        $this->addSql('ALTER TABLE note ALTER slug TYPE VARCHAR(255)');
    }
}
