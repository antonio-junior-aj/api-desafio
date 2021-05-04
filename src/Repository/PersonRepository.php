<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Person::class);
    }

    public function findAllQueryBuilder($filter = null, $page = 1) {
        $qb = $this->createQueryBuilder('person');
        if ($id = $filter->get("id")) {
            $qb->andWhere('person.id = :filter_id')
                    ->setParameter('filter_id', $id);
        }
        if ($type = $filter->get("type")) {
            $qb->andWhere('person.type = :filter_type')
                    ->setParameter('filter_type', $type);
        }
        if ($filter->get("cpf_cnpj")) {
            if ($filter->get("type") == "Física") {
                $cpf_cnpj = \App\Util\MaskUtil::unmaskCpf($filter->get("cpf_cnpj"));
            }
            if ($filter->get("type") == "Jurídica") {
                $cpf_cnpj = \App\Util\MaskUtil::unmaskCnpj($filter->get("cpf_cnpj"));
            }

            $qb->andWhere('person.cpf_cnpj LIKE :filter_cpf_cnpj')
                    ->setParameter('filter_cpf_cnpj', '%' . $cpf_cnpj . '%');
        }
        if ($blacklist = $filter->get("blacklist")) {
            $qb->andWhere('person.blacklist = :filter_blacklist')
                    ->setParameter('filter_blacklist', $blacklist);
        }
//        dd($qb->getQuery()->getSQL()); # debug

        $paginator = $this->paginate($qb, $page);

        return $paginator;
    }

    public function paginate($dql, $page = 1, $limit = 5) {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
                ->setFirstResult($limit * ($page - 1)) // Offset
                ->setMaxResults($limit); // Limit

        return $paginator;
    }

    public function getLastOrder() {
        $dql = '
                SELECT MAX(o.order_number) AS MAXIMO
                FROM App\Entity\Person o
                ';
        $query = $this->getEntityManager()->createQuery($dql);
        $max = $query->execute();
//        dd($max);
        return isset($max[0]['MAXIMO']) ? $max[0]['MAXIMO'] + 1 : 1;
    }

    public function reorder($ids) {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            foreach ($ids as $n => $id) {
                $person = $this->find($id);
                if ($person) {
                    $person->setOrderNumber($n + 1);
                    $em->persist($person);
                    $em->flush();
                }
            }
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }

    public function serializeObject($o, $isInsert = false) {
        $aPerson = $o;
        if ($o->getType() == "Física") {
            if ($isInsert) {
                $cpf_cnpj = \App\Util\MaskUtil::unmaskCpf($o->getCpfCnpj());
            } else {
                $cpf_cnpj = \App\Util\MaskUtil::maskCpf($o->getCpfCnpj());
            }
        }
        if ($o->getType() == "Jurídica") {
            if ($isInsert) {
                $cpf_cnpj = \App\Util\MaskUtil::unmaskCnpj($o->getCpfCnpj());
            } else {
                $cpf_cnpj = \App\Util\MaskUtil::maskCnpj($o->getCpfCnpj());
            }
        }
        $aPerson->setCpfCnpj($cpf_cnpj);
        return $aPerson;
    }

    public function getValid($data, $isEdit = false) {
        if (!$isEdit && empty($data->get("type"))) {
            throw new NotFoundHttpException('Tipo não preenchido!');
        }
        if (!$isEdit && empty($data->get("cpf_cnpj"))) {
            throw new \Exception("CPF/CNPJ não preenchido");
        }

        if (!$this->getValidCpfCnpj($data)) {
            throw new \Exception("CPF/CNPJ inválido");
        }

        # valida CPF/CNPJ para ficar unico
        $hasCpfCnpj = $this->findOneByCpfCnpj($data->get("type"), $data->get("cpf_cnpj"), $isEdit);        
        if ($hasCpfCnpj) {
            throw new \Exception("CPF/CNPJ já existente");
        }
    }

    public function getValidCpfCnpj($data) {
        if (!empty($data->get("cpf_cnpj")) && $data->get("type") == "Física" && !\App\Util\ValidateUtil::validaCpf(\App\Util\MaskUtil::unmaskCpf($data->get("cpf_cnpj")))) {
            return false;
        }
        if (!empty($data->get("cpf_cnpj")) && $data->get("type") == "Jurídica" && !\App\Util\ValidateUtil::validaCnpj(\App\Util\MaskUtil::unmaskCnpj($data->get("cpf_cnpj")))) {
            return false;
        }
        return true;
    }

    public function findOneByCpfCnpj($type, $cpfORcnpj, $id = null): ?Person {
        if ($type == "Física") {
            $cpf_cnpj = \App\Util\MaskUtil::unmaskCpf($cpfORcnpj);
        }
        if ($type == "Jurídica") {
            $cpf_cnpj = \App\Util\MaskUtil::unmaskCnpj($cpfORcnpj);
        }

        $query = $this->createQueryBuilder('p')
                ->andWhere('p.type = :type')
                ->setParameter('type', $type)
                ->andWhere('p.cpf_cnpj = :cpf_cnpj')
                ->setParameter('cpf_cnpj', $cpf_cnpj);
        if ($id) {
            $query->andWhere('p.id <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    public function checkType(){
        
    }
    // /**
    //  * @return Person[] Returns an array of Person objects
    //  */
    /*
      public function findByExampleField($value)
      {
      return $this->createQueryBuilder('p')
      ->andWhere('p.exampleField = :val')
      ->setParameter('val', $value)
      ->orderBy('p.id', 'ASC')
      ->setMaxResults(10)
      ->getQuery()
      ->getResult()
      ;
      }
     */

    /*
      public function findOneBySomeField($value): ?Person
      {
      return $this->createQueryBuilder('p')
      ->andWhere('p.exampleField = :val')
      ->setParameter('val', $value)
      ->getQuery()
      ->getOneOrNullResult()
      ;
      }
     */
}
