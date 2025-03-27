<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327160129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filesystem_file ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE filesystem_file ADD CONSTRAINT FK_47F0AE287E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_47F0AE287E3C61F9 ON filesystem_file (owner_id)');
        $this->addSql('ALTER TABLE note ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA147E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CFBDFA147E3C61F9 ON note (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filesystem_file DROP CONSTRAINT FK_47F0AE287E3C61F9');
        $this->addSql('DROP INDEX IDX_47F0AE287E3C61F9');
        $this->addSql('ALTER TABLE filesystem_file DROP owner_id');
        $this->addSql('ALTER TABLE note DROP CONSTRAINT FK_CFBDFA147E3C61F9');
        $this->addSql('DROP INDEX IDX_CFBDFA147E3C61F9');
        $this->addSql('ALTER TABLE note DROP owner_id');
    }
}
