<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    /**
     * Point d'entrée de connexion. Le corps JSON { email, password } est traité
     * par le firewall `login` (json_login), qui renvoie le token JWT.
     * Cette méthode n'est jamais exécutée.
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Interceptée par le firewall json_login.');
    }

    /** Profil de l'utilisateur connecté (utilisé par les fronts après login). */
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $establishment = $user->getEstablishment();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'fullName' => $user->getFullName(),
            'roles' => $user->getRoles(),
            'establishment' => $establishment ? [
                'id' => $establishment->getId(),
                'name' => $establishment->getName(),
                'slug' => $establishment->getSlug(),
            ] : null,
        ]);
    }
}
