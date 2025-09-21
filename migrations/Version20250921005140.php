<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250921005140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, department VARCHAR(255) NOT NULL, role_id INT DEFAULT NULL, state_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E9D60322AC (role_id), INDEX IDX_1483A5E95D83CC1 (state_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES roles (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E95D83CC1 FOREIGN KEY (state_id) REFERENCES states (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9D60322AC');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E95D83CC1');
        $this->addSql('DROP TABLE users');
    }
}
