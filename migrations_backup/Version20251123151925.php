<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123151925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Cette migration ne fait rien car toutes les tables ont déjà été créées dans les migrations précédentes
        // Version20251119102944 : category et product
        // Version20251119131917 : user
        // Version20251121090744 : media
    }

    public function down(Schema $schema): void
    {
        // Cette migration ne fait rien car toutes les tables ont déjà été créées dans les migrations précédentes
    }
}
