<?php

namespace App\Controller;

use App\Infrastructure\Persistence\Doctrine\Entities\RoleEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/roles')]
#[OA\Tag(name: 'Roles')]
class RoleController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'role_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Obtener lista de roles',
        description: 'Retorna todos los roles disponibles. Si no existen, crea Administrador y Empleado.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de roles',
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
        $repo = $this->em->getRepository(RoleEntity::class);
        $roles = $repo->findAll();

        if (empty($roles)) {
            $defaults = ['Administrador', 'Empleado'];
            foreach ($defaults as $name) {
                $role = new RoleEntity($name);
                $role->setName($name);
                $this->em->persist($role);
            }
            $this->em->flush();
            $roles = $repo->findAll();
        }

        $data = array_map(fn($r) => ['id' => $r->getId(), 'name' => $r->getName()], $roles);

        return $this->json($data);
    }
}
