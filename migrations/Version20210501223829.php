<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210501223829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL COMMENT \'Índice da tabela\', type VARCHAR(8) NOT NULL COMMENT \'Tipo de pessoa: F- Física (tem CPF); J- Jurídica (tem CNPJ)\', cpf_cnpj VARCHAR(14) NOT NULL COMMENT \'CPF: 111.111.112-00(grava sem máscara); CNPJ: 55.238.879/0001-04(grava sem máscara)\', blacklist TINYINT(1) NOT NULL COMMENT \'Boleano para adicionar a blacklist\', blacklist_reason LONGTEXT DEFAULT NULL COMMENT \'Razão para entrar na blacklist(opcional)\', order_number INT NOT NULL COMMENT \'Ordenação manual dos dados\', created_at DATETIME NOT NULL COMMENT \'Data de inserção\', updated_at DATETIME DEFAULT NULL COMMENT \'Data de alteração\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE person');
    }
}
