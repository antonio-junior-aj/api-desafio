<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Person;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/persons", name="persons_")
 */
class PersonController extends AbstractController
{

    /**
     * ROTA 1 - index (retorna as pessoas cadastradas)
     * 
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Person::class);
//        $persons = $repository->findAll(); # get all

        if (!$repository->getValidCpfCnpj($request->query)) {
            throw new \Exception("CPF/CNPJ inválido");
        }

        $order = $request->request->has("ORDER") ? $request->request->get("ORDER") : "id";
        $persons = $repository->findAllQueryBuilder($request, $order, ($request->request->has("PAGE") ? $request->request->get("PAGE") : 1));

        if (!$persons->count()) {
            return new JsonResponse('Sem registros encontrados', Response::HTTP_NO_CONTENT);
        }

        return $this->json(['data' => $persons]);
    }

    /**
     * ROTA 2 - show (retorna a pessoas cadastrada)
     * 
     * @Route("/{personId}", name="show", methods={"GET"})
     */
    public function show($personId): Response
    {
        $person = $this->getDoctrine()->getRepository(Person::class)->find($personId);

        if (!$person) {
            return new JsonResponse('Sem registro encontrado para o índice: ' . $personId, Response::HTTP_NO_CONTENT);
        }

        return $this->json(['data' => $person]);
    }

    /**
     * ROTA 3 - create (cria nova pessoa)
     * 
     * @Route("/", name="create", methods={"POST"})
     */
    public function create(Request $request, ValidatorInterface $validator): Response
    {
//        $data = json_decode($request->getContent(), true); # json
        $repository = $this->getDoctrine()->getRepository(Person::class);

        # validacao manual PHP
        try {
            $repository->getValid($request->request);
        } catch (\Exception $ex) {
            return new JsonResponse(['errors' => $ex->getMessage(),], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->request->all();
        $person = new Person();
        $person->setType(isset($data["type"]) ? $data["type"] : '');
        $person->setCpfCnpj(isset($data["value"]) ? $data["value"] : '');
        $person->setBlacklist(false);
        $person->setOrderNumber($repository->getLastOrder());
        $person->setCreatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));
        $aSerializable = $repository->serializeObject($person);

        # validacaçao doctrine annotation
        $errors = $validator->validate($aSerializable);
        if (count($errors) > 0) {
            $vError = array_map(function ($error) {
                return $error->getMessage();
            }, $errors->getIterator()->getArrayCopy());
            return new JsonResponse(['errors' => $vError,], Response::HTTP_BAD_REQUEST);
        }

        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->persist($aSerializable);
        $doctrine->flush();

        return $this->json(['data' => 'Pessoa cadastrada']);
    }

    /**
     * ROTA 4 - update (atualiza pessoa)
     * 
     * @Route("/{personId}", name="update",  methods={"PUT", "PATCH"})
     */
    public function update($personId, Request $request, ValidatorInterface $validator): Response
    {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Person::class);

        # validacao manual PHP
        try {
            $repository->getValid($request->request, $personId);
        } catch (\Exception $ex) {
            return new JsonResponse(['errors' => $ex->getMessage(),], Response::HTTP_BAD_REQUEST);
        }

        $data = $request->request->all();
        $person = $repository->find($personId);
        
        if (!$person) {
            return new JsonResponse('Sem registro encontrado', Response::HTTP_NO_CONTENT);
        }
        
        if ($request->request->has("type")) {
            $person->setType($data["type"]);
        }

        if ($request->request->has("value")) {
            $person->setCpfCnpj($data["value"]);
            $person = $repository->serializeObject($person);
        }

        $person->setUpdatedAt(new \Datetime('now', new \DateTimezone('America/Sao_Paulo')));

        # validacaçao doctrine annotation
        $errors = $validator->validate($person);
        if (count($errors) > 0) {
            $vError = array_map(function ($error) {
                return $error->getMessage();
            }, $errors->getIterator()->getArrayCopy());
            return new JsonResponse(['errors' => $vError,], Response::HTTP_BAD_REQUEST);
        }

        $manager = $doctrine->getManager();
        $manager->flush();

        return $this->json(['data' => 'Pessoa editada']);
    }

    /**
     * ROTA 5 - delete (deleta a pessoa)
     * 
     * @Route("/{personId}", name="delete", methods={"DELETE"})
     */
    public function delete($personId): Response
    {
        $doctrine = $this->getDoctrine();
        $person = $this->getDoctrine()->getRepository(Person::class)->find($personId);

        if (!$person) {
            return new JsonResponse('Sem registro encontrado', Response::HTTP_NO_CONTENT);
        }

        $manager = $doctrine->getManager();
        $manager->remove($person);
        $manager->flush();

        return $this->json(['data' => 'Pessoa excluída']);
    }

    /**
     * ROTA 6 - blacklist (marca/desmarca usuário na blacklist)
     * 
     * @Route("/{personId}/blacklist", name="blacklist",  methods={"PUT", "PATCH"})
     */
    public function blacklist($personId, Request $request): Response
    {
        $doctrine = $this->getDoctrine();
        $repository = $doctrine->getRepository(Person::class);

        $data = $request->request->all();
        $person = $repository->find($personId);

        if (!$person) {
            return new JsonResponse('Sem registro encontrado', Response::HTTP_NO_CONTENT);
        }

        $person->setBlacklist($data["blacklist"]);
        if ($data["blacklist"]) {
            $person->setBlacklistReason($data["reason"]);
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
     * ROTA 7 - reorder (reordena os registros)
     * 
     * @Route("/reorder", name="reorder", methods={"POST"})
     */
    public function reorder(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Person::class);
        $data = $request->request->all();

        if (!$data["ids"]) {
            return new JsonResponse('Sem IDs enviado', Response::HTTP_NO_CONTENT);
        }
        $repository->reorder($data["ids"]);

        return $this->json(['data' => 'Pessoas reordenadas']);
    }
}
