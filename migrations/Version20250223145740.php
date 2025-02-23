<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223145740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_selected_options (answer_id INT NOT NULL, answer_option_id INT NOT NULL, INDEX IDX_DB2FFB6CAA334807 (answer_id), INDEX IDX_DB2FFB6C9A3BC2B9 (answer_option_id), PRIMARY KEY(answer_id, answer_option_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer_selected_options ADD CONSTRAINT FK_DB2FFB6CAA334807 FOREIGN KEY (answer_id) REFERENCES answer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer_selected_options ADD CONSTRAINT FK_DB2FFB6C9A3BC2B9 FOREIGN KEY (answer_option_id) REFERENCES answer_option (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25FFBB0E84');
        $this->addSql('DROP INDEX IDX_DADD4A25FFBB0E84 ON answer');
        $this->addSql('ALTER TABLE answer DROP selected_option_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_selected_options DROP FOREIGN KEY FK_DB2FFB6CAA334807');
        $this->addSql('ALTER TABLE answer_selected_options DROP FOREIGN KEY FK_DB2FFB6C9A3BC2B9');
        $this->addSql('DROP TABLE answer_selected_options');
        $this->addSql('ALTER TABLE answer ADD selected_option_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25FFBB0E84 FOREIGN KEY (selected_option_id) REFERENCES answer_option (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_DADD4A25FFBB0E84 ON answer (selected_option_id)');
    }
}
