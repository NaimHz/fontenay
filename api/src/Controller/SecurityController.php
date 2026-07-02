<?php

namespace App\Controller;

use App\Entity\User;
use App\OpenApi\Schema\UserProfileSchema;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security as ApiSecurity;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Authentification')]
class SecurityController extends AbstractController
{
    /**
     * Point d'entrée de connexion. Le corps JSON { email, password } est traité
     * par le firewall `login` (json_login), qui renvoie le token JWT.
     * Cette méthode n'est jamais exécutée.
     */
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    #[ApiSecurity(name: null)]
    #[OA\RequestBody(content: new OA\JsonContent(
        required: ['email', 'password'],
        properties: [
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'password', type: 'string', format: 'password'),
        ],
    ))]
    #[OA\Response(
        response: 200,
        description: 'Connexion réussie.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'token', type: 'string', description: 'Token JWT à utiliser en en-tête Authorization: Bearer.'),
        ]),
    )]
    #[OA\Response(response: 401, description: 'Identifiants invalides.')]
    public function login(): never
    {
        throw new \LogicException('Interceptée par le firewall json_login.');
    }

    /** Profil de l'utilisateur connecté (utilisé par les fronts après login). */
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    #[OA\Response(response: 200, description: "Profil de l'utilisateur authentifié.", content: new Model(type: UserProfileSchema::class))]
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
