<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140624220434 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE SEQUENCE events_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE talks_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE venues_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE TABLE events (id INT NOT NULL, venue_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, startDate TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, endDate TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_5387574A40A73EBA ON events (venue_id)");
        $this->addSql("CREATE TABLE events_attendees (event_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(event_id, user_id))");
        $this->addSql("CREATE INDEX IDX_513D0E0771F7E88B ON events_attendees (event_id)");
        $this->addSql("CREATE INDEX IDX_513D0E07A76ED395 ON events_attendees (user_id)");
        $this->addSql("CREATE TABLE talks (id INT NOT NULL, event_id INT DEFAULT NULL, speaker_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, abstract TEXT NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_472281DA71F7E88B ON talks (event_id)");
        $this->addSql("CREATE INDEX IDX_472281DAD04A0F27 ON talks (speaker_id)");
        $this->addSql("CREATE TABLE venues (id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("ALTER TABLE events ADD CONSTRAINT FK_5387574A40A73EBA FOREIGN KEY (venue_id) REFERENCES venues (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE events_attendees ADD CONSTRAINT FK_513D0E0771F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE events_attendees ADD CONSTRAINT FK_513D0E07A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE talks ADD CONSTRAINT FK_472281DA71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE talks ADD CONSTRAINT FK_472281DAD04A0F27 FOREIGN KEY (speaker_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("ALTER TABLE events_attendees DROP CONSTRAINT FK_513D0E0771F7E88B");
        $this->addSql("ALTER TABLE talks DROP CONSTRAINT FK_472281DA71F7E88B");
        $this->addSql("ALTER TABLE events DROP CONSTRAINT FK_5387574A40A73EBA");
        $this->addSql("DROP SEQUENCE events_id_seq CASCADE");
        $this->addSql("DROP SEQUENCE talks_id_seq CASCADE");
        $this->addSql("DROP SEQUENCE venues_id_seq CASCADE");
        $this->addSql("DROP TABLE events");
        $this->addSql("DROP TABLE events_attendees");
        $this->addSql("DROP TABLE talks");
        $this->addSql("DROP TABLE venues");
    }
}
