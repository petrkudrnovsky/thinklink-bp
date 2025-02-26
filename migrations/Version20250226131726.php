<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226131726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Custom migration: Change term_frequencies column type from text(array) to json with custom clausule: USING term_frequencies::JSON';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tf_idf_vector ALTER term_frequencies TYPE JSON USING term_frequencies::JSON');
        $this->addSql('COMMENT ON COLUMN tf_idf_vector.term_frequencies IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tf_idf_vector ALTER term_frequencies TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN tf_idf_vector.term_frequencies IS \'(DC2Type:simple_array)\'');
    }
}
