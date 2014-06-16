<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140617020043 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("CREATE SEQUENCE ext_log_entries_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        $this->addSql("CREATE TABLE ext_log_entries (id INT NOT NULL, action VARCHAR(8) NOT NULL, logged_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(255) NOT NULL, version INT NOT NULL, data TEXT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX log_class_lookup_idx ON ext_log_entries (object_class)");
        $this->addSql("CREATE INDEX log_date_lookup_idx ON ext_log_entries (logged_at)");
        $this->addSql("CREATE INDEX log_user_lookup_idx ON ext_log_entries (username)");
        $this->addSql("CREATE INDEX log_version_lookup_idx ON ext_log_entries (object_id, object_class, version)");
        $this->addSql("COMMENT ON COLUMN ext_log_entries.data IS '(DC2Type:array)'");
        $this->addSql("CREATE TABLE users (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, salt VARCHAR(255) NOT NULL, fname VARCHAR(120) DEFAULT NULL, lname VARCHAR(120) DEFAULT NULL, enabled BOOLEAN NOT NULL, expired BOOLEAN NOT NULL, expiresAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, credentialsExpired BOOLEAN NOT NULL, credentialsExpireAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locked BOOLEAN NOT NULL, roles TEXT NOT NULL, lastLogin TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, passwordRequestedAt TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))");
        $this->addSql("COMMENT ON COLUMN users.roles IS '(DC2Type:array)'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        
        $this->addSql("DROP SEQUENCE ext_log_entries_id_seq CASCADE");
        $this->addSql("DROP SEQUENCE users_id_seq CASCADE");
        $this->addSql("DROP TABLE ext_log_entries");
        $this->addSql("DROP TABLE users");
    }
}
