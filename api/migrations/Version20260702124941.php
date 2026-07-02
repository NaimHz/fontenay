<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702124941 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dining_table ADD server_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dining_table ADD CONSTRAINT FK_25538021844E6B7 FOREIGN KEY (server_id) REFERENCES "user" (id)');
        $this->addSql('CREATE INDEX IDX_25538021844E6B7 ON dining_table (server_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dining_table DROP CONSTRAINT FK_25538021844E6B7');
        $this->addSql('DROP INDEX IDX_25538021844E6B7');
        $this->addSql('ALTER TABLE dining_table DROP server_id');
    }
}
