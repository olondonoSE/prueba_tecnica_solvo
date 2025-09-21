<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Application\Command\CreateUserCommand;
use App\Application\Command\UpdateUserCommand;
use App\Application\Command\ChangeUserStateCommand;
use App\Application\Handler\ChangeUserStateHandler;
use App\Application\Command\DeleteUserCommand;
use App\Application\Handler\DeleteUserHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/users')]
#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    public function __construct(private MessageBusInterface $bus, private \App\Domain\Repository\UserRepositoryInterface $repo) {}

    // ---------- CREATE ----------
    #[Route('', name: 'user_new', methods: ['POST'])]
    #[OA\Post(
        summary: 'Crear usuario',
        description: 'Crea un nuevo usuario en el sistema',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name','email','department','roleId','stateId'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'department', type: 'string'),
                    new OA\Property(property: 'roleId', type: 'integer'),
                    new OA\Property(property: 'stateId', type: 'integer')
                ],
                example: [
                    'name' => 'Juan Pérez',
                    'email' => 'juan@example.com',
                    'department' => 'IT',
                    'roleId' => 2,
                    'stateId' => 1
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario creado exitosamente',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'status', type: 'string')],
                    example: ['status' => 'created']
                )
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // validate...
        $cmd = new CreateUserCommand(
            $data['name'],
            $data['email'],
            $data['department'],
            (int)$data['roleId'],
            (int)$data['stateId']
        );
        // Could dispatch to bus or call handler directly
        $this->bus->dispatch($cmd);
        return $this->json(['status'=>'created'], 201);
    }

     // ---------- CHANGE STATE ----------
    #[Route('/{id}/change-state', name: 'user_change_state', methods: ['PATCH'])]
    #[OA\Patch(
        summary: 'Cambiar estado de usuario',
        description: 'Cambia el estado de un usuario existente',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['stateId'],
                properties: [new OA\Property(property: 'stateId', type: 'integer')],
                example: ['stateId' => 2]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado')
        ]
    )]
    public function changeState(int $id, Request $request, ChangeUserStateHandler $handler): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $cmd = new ChangeUserStateCommand($id, (int)$payload['stateId']);
        // Synchronous call:
        $handler($cmd);
        return $this->json(['status'=>'ok']);
    }

    #[Route('', name: 'user_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Obtener listado de usuarios',
        description: 'Retorna una lista de todos los usuarios registrados en el sistema con sus roles y estados',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de usuarios obtenida exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
                                    new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
                                    new OA\Property(property: 'department', type: 'string', example: 'IT'),
                                    new OA\Property(
                                        property: 'role',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'Admin')
                                        ],
                                        type: 'object'
                                    ),
                                    new OA\Property(
                                        property: 'state',
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 1),
                                            new OA\Property(property: 'name', type: 'string', example: 'Active')
                                        ],
                                        type: 'object'
                                    )
                                ]
                            )
                        ),
                        new OA\Property(property: 'count', type: 'integer', example: 5)
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno del servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Error retrieving users: Database connection failed')
                    ]
                )
            )
        ]
    )]
    public function list(): JsonResponse
    {
        try {
            // Obtener todos los usuarios del repositorio
            $users = $this->repo->findAll();

            // Convertir los objetos de dominio a array para la respuesta JSON
            $usersArray = array_map(function ($user) {
                return [
                    'id' => $user->id(),
                    'name' => $user->name(),
                    'email' => $user->email(),
                    'department' => $user->department(),
                    'role' => [
                        'id' => $user->role()->id(),
                        'name' => $user->role()->name()
                    ],
                    'state' => [
                        'id' => $user->state()->id(),
                        'name' => $user->state()->name()
                    ]
                ];
            }, $users);

            return $this->json([
                'success' => true,
                'data' => $usersArray,
                'count' => count($usersArray)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error retrieving users: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------- UPDATE ----------
    #[Route('/{id}', name: 'user_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Actualizar un usuario existente',
        description: 'Actualiza la información de un usuario específico por ID',
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del usuario a actualizar',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Datos del usuario a actualizar',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez Updated'),
                    new OA\Property(property: 'email', type: 'string', example: 'juan.updated@example.com'),
                    new OA\Property(property: 'department', type: 'string', example: 'IT Updated'),
                    new OA\Property(property: 'roleId', type: 'integer', example: 2),
                    new OA\Property(property: 'stateId', type: 'integer', example: 1)
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuario actualizado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado')
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            // Obtener y validar datos del request
            $data = json_decode($request->getContent(), true);

            $cmd = new UpdateUserCommand(
                $id,
                $data['name'],
                $data['email'],
                $data['department'],
                (int)$data['roleId'],
                (int)$data['stateId']
            );
            // Could dispatch to bus or call handler directly
            $this->bus->dispatch($cmd);
            return $this->json(['status'=>'updated'], 201);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    // ---------- DELETE ----------
    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Eliminar usuario',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Usuario eliminado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado')
        ]
    )]
    public function delete(int $id, DeleteUserHandler $handler): JsonResponse
    {
        $cmd = new DeleteUserCommand($id);
        // Synchronous call:
        $handler($cmd);
        return $this->json([
                'success' => true,
                'description' => 'Usuario eliminado'
            ], 204);
    }

    #[Route('/test', name: 'user_test', methods: ['GET'])]
    #[OA\Get(
        summary: 'Endpoint de prueba',
        description: 'Endpoint para verificar que la API está funcionando correctamente',
        tags: ['Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Prueba exitosa',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'ok', type: 'boolean')
                    ],
                    example: ['ok' => true]
                )
            )
        ]
    )]
    public function test(): JsonResponse
    {
        return $this->json(['ok' => true]);
    }
}
