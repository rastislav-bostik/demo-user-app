<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240729105227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, name, surname, email, gender, note, active, roles FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id BLOB NOT NULL --(DC2Type:uuid)
        , name VARCHAR(48) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, note VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL, roles CLOB NOT NULL --(DC2Type:simple_array)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO user (id, name, surname, email, gender, note, active, roles) SELECT id, name, surname, email, gender, note, active, roles FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, name, surname, email, gender, roles, note, active FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(48) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, gender VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:simple_array)
        , note VARCHAR(255) DEFAULT NULL, active BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO user (id, name, surname, email, gender, roles, note, active) SELECT id, name, surname, email, gender, roles, note, active FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
    }
}
