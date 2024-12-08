<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;



class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;
    private UserPasswordHasherInterface $userPasswordHasher;


    public function __construct(EntityManagerInterface $entityManager,
                                JWTTokenManagerInterface $jwtManager,
                                UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    /**
     * @throws JsonException
     */
    #[Route('/api/new', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email и пароль обязательны'], 400);
        }

        $user = new User();
        // тут можно проверку на соответствие email сделать
        $user->setEmail($data['email']);
        if (isset($data['info'])) {
            $user->setInfo($data['info']);
        }
        $user->setRoles(['ROLE_USER']);
        $hashedPassword = $this->userPasswordHasher->hashPassword($user,$data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Пользователь создан успешно!'], 201);
    }

    /**
     * @throws JsonException
     */
    #[Route('/api/users/{id}', methods: ['PUT'])]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'Пользователь не найден'], 404);
        }

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $data['password']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Обновлено']);
    }

    #[Route('/api/users/{id}', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'Пользователь не найден!'], 404);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Пользователь удален']);
    }

    /**
     * @throws JsonException
     */
    #[Route('/api/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email и пароль обязательны!'], 400);
        }

        // Проверяем пользователя
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user || !$this->userPasswordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Неверные логин или пароль!'], 401);
        }

        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
    #[Route('/api/users/{id}', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'Пользователь не найден!'], 404);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'info' => $user->getInfo(),
        ]);
    }
}
