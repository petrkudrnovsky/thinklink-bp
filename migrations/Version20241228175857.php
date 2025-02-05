<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241228175857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id SERIAL NOT NULL, filename VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, data BYTEA NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE note ALTER slug TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE slug_sequence ALTER slug TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE note ALTER slug TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE slug_sequence ALTER slug TYPE VARCHAR(255)');
    }
}
