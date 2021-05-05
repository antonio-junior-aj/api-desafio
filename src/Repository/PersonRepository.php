<?php
namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Função que retorna um array de pessoas, podendo ser filtrado, ordenado e paginado
     * 
     * @param string $filter
     * @param string $order
     * @param int $page
     * @return retorna um elemento $paginator com o objeto Person
     */
    public function findAllQueryBuilder($filter = null, $order = "id", $page = 1)
    {
        $qb = $this->createQueryBuilder('person');
        $id = $filter->get("id");
        if ($id) {
            $qb->andWhere('person.id = :filter_id')
                ->setParameter('filter_id', $id);
        }
        $type = $filter->get("type");
        if ($type) {
            $qb->andWhere('person.type = :filter_type')
                ->setParameter('filter_type', $type);
        }
        if ($filter->get("value")) {
            if ($filter->get("type") == Person::TIPO_FISICO) {
                $cpf_cnpj = \App\Util\MaskUtil::unmaskCpf($filter->get("value"));
            }
            if ($filter->get("type") == Person::TIPO_JURIDICO) {
                $cpf_cnpj = \App\Util\MaskUtil::unmaskCnpj($filter->get("value"));
            }

            $qb->andWhere('person.cpf_cnpj LIKE :filter_cpf_cnpj')
                ->setParameter('filter_cpf_cnpj', '%' . $cpf_cnpj . '%');
        }
        $blacklist = $filter->get("blacklist");
        if ($blacklist) {
            $qb->andWhere('person.blacklist = :filter_blacklist')
                ->setParameter('filter_blacklist', $blacklist);
        }

        $qb->addOrderBy("person." . $order);

        $paginator = $this->paginate($qb, $page);

        return $paginator;
    }

    /**
     * Função que página a partir do querybuilder, podendo passar a página desejada e a quantidade de itens
     * 
     * @param QueryBuilder $qb
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function paginate($qb, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($qb);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

    /**
     * Função que retorna a ordem mais alta cadastrada +1
     * 
     * @return int ordem mais alta ou 1 caso não tenha
     */
    public function getLastOrder()
    {
        $dql = '
                SELECT MAX(o.order_number) AS MAXIMO
                FROM App\Entity\Person o
                ';
        $query = $this->getEntityManager()->createQuery($dql);
        $max = $query->execute();
        return isset($max[0]['MAXIMO']) ? $max[0]['MAXIMO'] + 1 : 1;
    }

    /**
     * Função que reordena os itens de acordo com os IDs passados
     * 
     * @param array $ids
     * @throws \App\Repository\Exception
     */
    public function reorder($ids)
    {
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

    /**
     * Função que trata o objeto para inserir no banco
     * 
     * @param object $person
     * @return object $person tratado
     */
    public function serializeObject($person)
    {
        if ($person->getType() == Person::TIPO_FISICO) {
            $cpf_cnpj = \App\Util\MaskUtil::unmaskCpf($person->getCpfCnpj());
        }
        if ($person->getType() == Person::TIPO_JURIDICO) {
            $cpf_cnpj = \App\Util\MaskUtil::unmaskCnpj($person->getCpfCnpj());
        }
        if (isset($cpf_cnpj)) {
            $person->setCpfCnpj($cpf_cnpj);
        }

        return $person;
    }

    /**
     * Função que valida os campos obrigatórios, verifica se o CPF/CNPJ é válido e único
     * 
     * @param request $data
     * @param bool $isEdit
     * @throws \Exception
     */
    public function getValid($data, $isEdit = false)
    {
        /*
          if (!$isEdit && empty($data->get("type"))) {
          throw new \Exception('Tipo não preenchido');
          }
          if (!empty($data->get("type")) && !in_array($data->get("type"), array_keys(Person::$_TIPO))) {
          throw new \Exception("Selecione Pessoa Física(" . Person::TIPO_FISICO . ") ou Jurídica(" . Person::TIPO_JURIDICO . ")");
          }

          if (!$isEdit && empty($data->get("value"))) {
          throw new \Exception("CPF/CNPJ não preenchido");
          }
         */

        if (!$this->getValidCpfCnpj($data)) {
            throw new \Exception("CPF/CNPJ inválido");
        }

        # valida CPF/CNPJ para ficar unico
        $hasCpfCnpj = $this->findOneByCpfCnpj($data->get("type"), $data->get("value"), $isEdit);
        if ($hasCpfCnpj) {
            throw new \Exception("CPF/CNPJ já existente");
        }
    }

    /**
     * Função que válida o CPF/CNPJ pelo tipo, informando se é válido ou não
     * 
     * @param request $data
     * @return boolean
     */
    public function getValidCpfCnpj($data)
    {
        if (!empty($data->get("value")) && $data->get("type") == Person::TIPO_FISICO && !\App\Util\ValidateUtil::validaCpf(\App\Util\MaskUtil::unmaskCpf($data->get("value")))) {
            return false;
        }
        if (!empty($data->get("value")) && $data->get("type") == Person::TIPO_JURIDICO && !\App\Util\ValidateUtil::validaCnpj(\App\Util\MaskUtil::unmaskCnpj($data->get("value")))) {
            return false;
        }
        return true;
    }

    /**
     * Funciona que verifica se a pessoa é única pelo tipo e CPF/CNPJ
     * 
     * @param string $type (F ou J)
     * @param string $cpfORcnpj
     * @param int $id
     * @return Person|null
     */
    public function findOneByCpfCnpj($type, $cpfORcnpj, $id = null): ?Person
    {
        if ($type == Person::TIPO_FISICO) {
            $cpf_cnpj = \App\Util\MaskUtil::unmaskCpf($cpfORcnpj);
        }
        if ($type == Person::TIPO_JURIDICO) {
            $cpf_cnpj = \App\Util\MaskUtil::unmaskCnpj($cpfORcnpj);
        }

        $query = $this->createQueryBuilder('p')
            ->andWhere("p.type = :type")
            ->setParameter('type', $type)
            ->andWhere("p.cpf_cnpj = :cpf_cnpj")
            ->setParameter('cpf_cnpj', $cpf_cnpj);
        if ($id) {
            $query->andWhere("p.id <> :id")
                ->setParameter('id', $id);
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}