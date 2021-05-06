<?php
namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Util\MaskUtil;

class PersonRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Função que retorna um array de pessoas, podendo ser filtrado, ordenado e paginado
     * 
     * @param string $data
     * @param string $order
     * @param int $page
     * @return retorna um elemento $paginator com o objeto Person
     */
    public function findAllQueryBuilder($data = null, $order = "id", $page = 1)
    {
        $qb = $this->createQueryBuilder('person');
        $id = isset($data["id"]) ? $data["id"] : null;
        if ($id) {
            $qb->andWhere('person.id = :filter_id')
                ->setParameter('filter_id', $id);
        }
        $type = isset($data["type"]) ? $data["type"] : null;
        if ($type) {
            $qb->andWhere('person.type = :filter_type')
                ->setParameter('filter_type', $type);
        }
        if (isset($data["value"]) && $type) {
            if ($type == Person::TIPO_FISICO) {
                $cpf_cnpj = MaskUtil::unmaskCpf($data["value"]);
            }
            if ($type == Person::TIPO_JURIDICO) {
                $cpf_cnpj = MaskUtil::unmaskCnpj($data["value"]);
            }

            $qb->andWhere('person.cpf_cnpj LIKE :filter_cpf_cnpj')
                ->setParameter('filter_cpf_cnpj', '%' . $cpf_cnpj . '%');
        }
        $blacklist = isset($data["blacklist"]) ? $data["blacklist"] : null;
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
     * Função que verifica se a pessoa é única pelo tipo e CPF/CNPJ
     * 
     * @param string $type (F ou J)
     * @param string $cpfORcnpj
     * @param int $id
     * @return Person|null
     */
    public function findOneByCpfCnpj($type, $cpfORcnpj, $id = null): ?Person
    {
        $cpf_cnpj = 0;
        if ($type == Person::TIPO_FISICO) {
            $cpf_cnpj = MaskUtil::unmaskCpf($cpfORcnpj);
        }
        if ($type == Person::TIPO_JURIDICO) {
            $cpf_cnpj = MaskUtil::unmaskCnpj($cpfORcnpj);
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

    /**
     * Função que trata o objeto para inserir no banco
     * 
     * @param object $person
     * @return object $person tratado
     */
    public function serializeObject($person)
    {
        if ($person->getType() == Person::TIPO_FISICO) {
            $cpf_cnpj = MaskUtil::unmaskCpf($person->getCpfCnpj());
        }
        if ($person->getType() == Person::TIPO_JURIDICO) {
            $cpf_cnpj = MaskUtil::unmaskCnpj($person->getCpfCnpj());
        }
        if (isset($cpf_cnpj)) {
            $person->setCpfCnpj($cpf_cnpj);
        }

        return $person;
    }

    /**
     * Função que verifica se o CPF/CNPJ é válido e único
     * 
     * @param request $data
     * @param int $id é o ID, se houver
     * @throws \Exception
     */
    public function getValid($data, $id = null)
    {
        if (!$this->getValidCpfCnpj($data)) {
            throw new \Exception("CPF/CNPJ inválido");
        }

        # valida CPF/CNPJ para ficar unico
        $hasCpfCnpj = $this->findOneByCpfCnpj($data["type"], $data["value"], $id);
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
        if (isset($data["value"]) && $data["value"] && isset($data["type"]) && $data["type"] == Person::TIPO_FISICO && !\App\Util\ValidateUtil::validaCpf(\App\Util\MaskUtil::unmaskCpf($data["value"]))) {
            return false;
        }
        if (isset($data["value"]) && $data["value"] && isset($data["type"]) && $data["type"] == Person::TIPO_JURIDICO && !\App\Util\ValidateUtil::validaCnpj(\App\Util\MaskUtil::unmaskCnpj($data["value"]))) {
            return false;
        }

        return true;
    }
}
