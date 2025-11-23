<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123154247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Conversion de VARCHAR vers JSON avec USING et rendre NOT NULL
        // Si la colonne est NULL ou vide, on utilise un tableau vide par défaut
        $this->addSql("ALTER TABLE over_account 
            ALTER endorsement TYPE JSON 
            USING CASE 
                WHEN endorsement IS NULL OR endorsement = '' THEN '[]'::json 
                ELSE endorsement::json 
            END");
        $this->addSql('ALTER TABLE over_account ALTER endorsement SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Conversion de JSON vers VARCHAR
        $this->addSql('ALTER TABLE over_account ALTER endorsement TYPE VARCHAR(255) USING endorsement::text');
        $this->addSql('ALTER TABLE over_account ALTER endorsement DROP NOT NULL');
    }
}
