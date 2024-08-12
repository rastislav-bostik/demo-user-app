<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240812083743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (id BLOB NOT NULL --(DC2Type:uuid)
        , name VARCHAR(48) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:simple_array)
        , note VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE INDEX i_users_gender ON users (gender)');
        $this->addSql('CREATE INDEX i_users_active ON users (active)');
        $this->addSql('DROP TABLE user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id BLOB NOT NULL --(DC2Type:uuid)
        , name VARCHAR(48) NOT NULL COLLATE "BINARY", surname VARCHAR(255) NOT NULL COLLATE "BINARY", email VARCHAR(255) NOT NULL COLLATE "BINARY", gender VARCHAR(255) NOT NULL COLLATE "BINARY", note VARCHAR(255) DEFAULT NULL COLLATE "BINARY", active BOOLEAN NOT NULL, roles CLOB NOT NULL COLLATE "BINARY" --(DC2Type:simple_array)
        , PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('DROP TABLE users');
    }
}
