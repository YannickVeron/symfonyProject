<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200421183908 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_9474526CA76ED395');
        $this->addSql('DROP INDEX IDX_9474526CFFDF7169');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, user_id, reply_to_id, text, movie_id FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, reply_to_id INTEGER DEFAULT NULL, text VARCHAR(255) NOT NULL COLLATE BINARY, movie_id INTEGER NOT NULL, CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CFFDF7169 FOREIGN KEY (reply_to_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO comment (id, user_id, reply_to_id, text, movie_id) SELECT id, user_id, reply_to_id, text, movie_id FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('CREATE INDEX IDX_9474526CFFDF7169 ON comment (reply_to_id)');
        $this->addSql('DROP INDEX IDX_55EEAC616A5458E8');
        $this->addSql('DROP INDEX IDX_55EEAC61A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__friend AS SELECT id, user_id, friend_id FROM friend');
        $this->addSql('DROP TABLE friend');
        $this->addSql('CREATE TABLE friend (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, friend_id INTEGER NOT NULL, status VARCHAR(255) NOT NULL, CONSTRAINT FK_55EEAC61A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_55EEAC616A5458E8 FOREIGN KEY (friend_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO friend (id, user_id, friend_id) SELECT id, user_id, friend_id FROM __temp__friend');
        $this->addSql('DROP TABLE __temp__friend');
        $this->addSql('CREATE INDEX IDX_55EEAC616A5458E8 ON friend (friend_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC61A76ED395 ON friend (user_id)');
        $this->addSql('DROP INDEX IDX_D8892622A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rating AS SELECT id, user_id, value, movie_id FROM rating');
        $this->addSql('DROP TABLE rating');
        $this->addSql('CREATE TABLE rating (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, value INTEGER NOT NULL, movie_id INTEGER NOT NULL, CONSTRAINT FK_D8892622A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO rating (id, user_id, value, movie_id) SELECT id, user_id, value, movie_id FROM __temp__rating');
        $this->addSql('DROP TABLE __temp__rating');
        $this->addSql('CREATE INDEX IDX_D8892622A76ED395 ON rating (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_9474526CA76ED395');
        $this->addSql('DROP INDEX IDX_9474526CFFDF7169');
        $this->addSql('CREATE TEMPORARY TABLE __temp__comment AS SELECT id, user_id, reply_to_id, text, movie_id FROM comment');
        $this->addSql('DROP TABLE comment');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, reply_to_id INTEGER DEFAULT NULL, text VARCHAR(255) NOT NULL, movie_id INTEGER NOT NULL)');
        $this->addSql('INSERT INTO comment (id, user_id, reply_to_id, text, movie_id) SELECT id, user_id, reply_to_id, text, movie_id FROM __temp__comment');
        $this->addSql('DROP TABLE __temp__comment');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('CREATE INDEX IDX_9474526CFFDF7169 ON comment (reply_to_id)');
        $this->addSql('DROP INDEX IDX_55EEAC61A76ED395');
        $this->addSql('DROP INDEX IDX_55EEAC616A5458E8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__friend AS SELECT id, user_id, friend_id FROM friend');
        $this->addSql('DROP TABLE friend');
        $this->addSql('CREATE TABLE friend (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, friend_id INTEGER NOT NULL)');
        $this->addSql('INSERT INTO friend (id, user_id, friend_id) SELECT id, user_id, friend_id FROM __temp__friend');
        $this->addSql('DROP TABLE __temp__friend');
        $this->addSql('CREATE INDEX IDX_55EEAC61A76ED395 ON friend (user_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC616A5458E8 ON friend (friend_id)');
        $this->addSql('DROP INDEX IDX_D8892622A76ED395');
        $this->addSql('CREATE TEMPORARY TABLE __temp__rating AS SELECT id, user_id, value, movie_id FROM rating');
        $this->addSql('DROP TABLE rating');
        $this->addSql('CREATE TABLE rating (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, value INTEGER NOT NULL, movie_id INTEGER NOT NULL)');
        $this->addSql('INSERT INTO rating (id, user_id, value, movie_id) SELECT id, user_id, value, movie_id FROM __temp__rating');
        $this->addSql('DROP TABLE __temp__rating');
        $this->addSql('CREATE INDEX IDX_D8892622A76ED395 ON rating (user_id)');
    }
}
