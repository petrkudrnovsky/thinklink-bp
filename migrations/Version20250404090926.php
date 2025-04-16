<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404090926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Connect TermStatistic to User (Generated)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE term_statistic ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE term_statistic ADD CONSTRAINT FK_A81867777E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A81867777E3C61F9 ON term_statistic (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE term_statistic DROP CONSTRAINT FK_A81867777E3C61F9');
        $this->addSql('DROP INDEX IDX_A81867777E3C61F9');
        $this->addSql('ALTER TABLE term_statistic DROP owner_id');
    }
}
