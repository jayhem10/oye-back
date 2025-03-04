<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController
{
    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Security $security)
    {
        // If user is already logged in
        if ($security->getUser()) {
            return new JsonResponse(['message' => 'Already logged in'], 200);
        }

        // Get login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            return new JsonResponse(['error' => $error->getMessageKey()], 401);
        }

        return new JsonResponse(['message' => 'Login successful'], 200);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]

    public function logout()
    {
        // Symfony will handle the logout
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Create a new User entity and populate it with the request data
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setPassword($passwordEncoder->encodePassword($user, $data['password']));
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setActive(true);

        // Persist the user in the database
        $entityManager->persist($user);
        $entityManager->flush();

        // Return a success response (you might also want to include the user data or an ID)
        return new JsonResponse(['message' => 'User registered successfully'], 201);
    }
}
