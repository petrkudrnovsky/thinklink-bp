<?php

declare(strict_types=1);

namespace migrations_old;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226123218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add term statistics (document frequency for each term) and term frequencies to the tf_idf_vector table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE term_statistic (id SERIAL NOT NULL, term VARCHAR(255) NOT NULL, document_frequency INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE tf_idf_vector ADD term_frequencies TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN tf_idf_vector.term_frequencies IS \'(DC2Type:simple_array)\'');

        $this->addSql('CREATE TABLE tf_idf_vector (id SERIAL NOT NULL, note_id INT DEFAULT NULL, vector vector(1000) DEFAULT NULL, term_frequencies JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1CD8961226ED0855 ON tf_idf_vector (note_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE term_statistic');
        $this->addSql('ALTER TABLE tf_idf_vector DROP term_frequencies');
    }
}
