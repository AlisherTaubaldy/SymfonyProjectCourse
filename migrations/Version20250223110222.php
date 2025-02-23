<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223110222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ADD selected_option_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25FFBB0E84 FOREIGN KEY (selected_option_id) REFERENCES answer_option (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_DADD4A25FFBB0E84 ON answer (selected_option_id)');
        $this->addSql('ALTER TABLE question DROP show_in_table');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD show_in_table TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25FFBB0E84');
        $this->addSql('DROP INDEX IDX_DADD4A25FFBB0E84 ON answer');
        $this->addSql('ALTER TABLE answer DROP selected_option_id');
    }
}
