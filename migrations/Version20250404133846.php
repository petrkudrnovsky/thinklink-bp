<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404133846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vector_embedding (id SERIAL NOT NULL, note_id INT NOT NULL, gemini_embedding vector(768) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81C152AE26ED0855 ON vector_embedding (note_id)');
        $this->addSql('ALTER TABLE vector_embedding ADD CONSTRAINT FK_81C152AE26ED0855 FOREIGN KEY (note_id) REFERENCES note (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vector_embedding DROP CONSTRAINT FK_81C152AE26ED0855');
        $this->addSql('DROP TABLE vector_embedding');
    }
}
