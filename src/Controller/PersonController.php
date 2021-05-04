<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Person;

/**
 * @Route("/persons", name="persons_")
 */
class PersonController extends AbstractController {

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request): Response {
        $repository = $this->getDoctrine()->getRepository(Person::class);
//        $persons = $repository->findAll(); # get all

        if (!$repository->getValidCpfCnpj($request->query)) {
            throw new \Exception("CPF/CNPJ inválido");
        }

        $persons = $repository->findAllQueryBuilder($request, ($request->request->has("PAGE") ? $request->request->get("PAGE") : 1));

        if (!$persons) {
            throw $this->createNotFoundException(
                    'Sem registros encontrados'
            );
        }

        $aPerson = ($persons->getQuery()->getResult());
        $aSerializable = array();
        foreach ($aPerson as $k => $person) {
            $aSerializable[] = $repository->serializeObject($person);
        }

        return $this->json(['data' => $aSerializable]);
    }

    /**
     * @Route("/{personId}", name="show", methods={"GET"})
     */
    public function show($personId): Response {
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $person = $this->getDoctrine()->getRepository(Person::class)->find($personId);

        if (!$person) {
            throw $this->createNotFoundException(
                    'Sem registro encontrado para o índice: ' . $personId
            );
        }

        $aSerializable = $repository->serializeObject($person);

        return $this->json(['data' => $aSerializable]);
    }

    /**
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request): Response {
        $repository = $this->getDoctrine()->getRepository(Person::class);

        try {
            $repository->getValid($request->request);
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

//        $data = json_decode($request->getContent(), true); # json
        $data = $request->request->all();
        $person = new Person();
        $person->setType($data["type"]);
        $person->setCpfCnpj($data["cpf_cnpj"]);
        $person->setBlacklist(false);
        $person->setOrderNumber($repository->getLastOrder());
        $person->setCreatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));
        $aSerializable = $repository->serializeObject($person, true);

        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->persist($aSerializable);
        $doctrine->flush();

        return $this->json(['data' => 'Pessoa cadastrada']);
    }

    /**
     * @Route("/{personId}", name="update",  methods={"PUT", "PATCH"})
     */
    public function update($personId, Request $request): Response {
        $repository = $this->getDoctrine()->getRepository(Person::class);

        try {
            $repository->getValid($request->request, true);
        } catch (Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        $data = $request->request->all();
        $doctrine = $this->getDoctrine();
        $person = $doctrine->getRepository(Person::class)->find($personId);
        if ($request->request->has("type"))
            $person->setType($data["type"]);

        if ($request->request->has("cpf_cnpj")) {
            $person->setCpfCnpj($data["cpf_cnpj"]);
            $person = $repository->serializeObject($person, $personId);
        }

        $person->setUpdatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));

        $manager = $doctrine->getManager();
        $manager->flush();

        return $this->json(['data' => 'Pessoa editada']);
    }

    /**
     * @Route("/{personId}", name="delete", methods={"DELETE"})
     */
    public function delete($personId): Response {
        $doctrine = $this->getDoctrine();
        $person = $this->getDoctrine()->getRepository(Person::class)->find($personId);

        $manager = $doctrine->getManager();
        $manager->remove($person);
        $manager->flush();

        return $this->json(['data' => 'Pessoa excluída']);
    }

    /**
     * @Route("/{personId}/blacklist", name="blacklist",  methods={"PUT", "PATCH"})
     */
    public function blacklist($personId, Request $request): Response {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Person::class);        

        $data = $request->request->all();
        $person = $repository->find($personId);
        $person->setBlacklist($data["blacklist"]);
        if ($data["blacklist"]) {
            $person->setBlacklistReason($data["blacklist_reason"]);
            $strBlacklist = "marcada";
        } else {
            $person->setBlacklist(false);
            $person->setBlacklistReason(null);
            $strBlacklist = "desmarcada";
        }

        $person->setUpdatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));

        $manager = $doctrine->getManager();
        $manager->flush();

        return $this->json(['data' => "Pessoa {$strBlacklist} na blacklist"]);
    }

    /**
     * @Route("/reorder", name="reorder", methods={"POST"})
     */
    public function reorder(Request $request): Response {
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $data = $request->request->all();

        if (!$data["ids"]) {
            throw $this->createNotFoundException(
                    'Sem IDs enviado'
            );
        }
        $repository->reorder($data["ids"]);

        return $this->json(['data' => 'Pessoas reordenadas']);
    }

}
