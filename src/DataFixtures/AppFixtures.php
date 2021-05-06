<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Person;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        # SQL MANUAL
        $manager->getConnection()->exec("
            INSERT INTO `api_desafio_test`.`person` (`id`, `type`, `cpf_cnpj`, `blacklist`, `blacklist_reason`, `order_number`, `created_at`) VALUES (1, 'F', '46290726080', 0, null, 1, now());
            INSERT INTO `api_desafio_test`.`person` (`id`, `type`, `cpf_cnpj`, `blacklist`, `blacklist_reason`, `order_number`, `created_at`) VALUES (2, 'F', '96091265024', 1, 'Blacklist física', 2, now());
            INSERT INTO `api_desafio_test`.`person` (`id`, `type`, `cpf_cnpj`, `blacklist`, `blacklist_reason`, `order_number`, `created_at`) VALUES (3, 'J', '87682597000180', 1, null, 3, now());
            INSERT INTO `api_desafio_test`.`person` (`id`, `type`, `cpf_cnpj`, `blacklist`, `blacklist_reason`, `order_number`, `created_at`) VALUES (4, 'J', '58235576000118', 1, 'Blacklist jurídica', 4, now());
        ");

        # MONTANDO ENTIDADE
        # pessoa fisica
        /*
          $person1 = new Person();
          $person1->setType("F");
          $person1->setCpfCnpj("11111111200");
          $person1->setBlacklist(false);
          $person1->setBlacklistReason(null);
          $person1->setOrderNumber(1);
          $person1->setCreatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));
          $person1->setUpdatedAt(null);

          $manager->persist($person1);
          $manager->flush();

         */

        # pessoa juridica
        /* $person2 = new Person();
          $person2->setType("J");
          $person2->setCpfCnpj("55238879000104");
          $person2->setBlacklist(true);
          $person2->setBlacklistReason("Motivo");
          $person2->setOrderNumber(2);
          $person2->setCreatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));
          $person2->setUpdatedAt(null);

          $manager->persist($person2);
          $manager->flush();
         * 
         */
    }
}
