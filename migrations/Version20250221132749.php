<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221132749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tf_idf_vector (id SERIAL NOT NULL, note_id INT DEFAULT NULL, vector vector(1000) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1CD8961226ED0855 ON tf_idf_vector (note_id)');
        $this->addSql('ALTER TABLE tf_idf_vector ADD CONSTRAINT FK_1CD8961226ED0855 FOREIGN KEY (note_id) REFERENCES note (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE tf_idf_vector DROP CONSTRAINT FK_1CD8961226ED0855');
        $this->addSql('DROP TABLE tf_idf_vector');
    }
}