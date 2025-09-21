<?php

namespace App\Controller;

use App\Infrastructure\Persistence\Doctrine\Entities\StateEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/states')]
#[OA\Tag(name: 'States')]
class StateController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'state_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Obtener lista de estados',
        description: 'Retorna todos los estados disponibles. Si no existen, crea Activo, Inactivo y Vacaciones.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de estados',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string')
                    ])
                )
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $repo = $this->em->getRepository(StateEntity::class);
        $states = $repo->findAll();

        if (empty($states)) {
            $defaults = ['Activo', 'Inactivo', 'Vacaciones'];
            foreach ($defaults as $name) {
                $state = new StateEntity($name);
                $state->setName($name);
                $this->em->persist($state);
            }
            $this->em->flush();
            $states = $repo->findAll();
        }

        $data = array_map(fn($s) => ['id' => $s->getId(), 'name' => $s->getName()], $states);

        return $this->json($data);
    }
}
