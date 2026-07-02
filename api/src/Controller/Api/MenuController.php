<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\OpenApi\Schema\DishSchema;
use App\Repository\DishRepository;
use App\Service\ApiNormalizer;
use App\Service\EstablishmentResolver;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** La carte de l'établissement, pour la prise de commande. */
#[Route('/api')]
#[OA\Tag(name: 'Menu')]
class MenuController extends AbstractController
{
    public function __construct(
        private readonly DishRepository $dishes,
        private readonly EstablishmentResolver $resolver,
        private readonly ApiNormalizer $normalizer,
    ) {
    }

    #[Route('/dishes', name: 'api_dishes', methods: ['GET'])]
    #[OA\Parameter(
        name: 'establishmentId',
        description: "Requis si l'utilisateur est rattaché à plusieurs établissements.",
        in: 'query',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Plats actifs de la carte, triés par catégorie puis par nom.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: DishSchema::class))),
    )]
    #[OA\Response(response: 400, description: 'Établissement non déterminé.')]
    public function dishes(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $establishment = $this->resolver->resolve($user, $request->query->getInt('establishmentId') ?: null);
        if (!$establishment) {
            return $this->json(['error' => 'Établissement non déterminé.'], 400);
        }

        $dishes = $this->dishes->findBy(
            ['establishment' => $establishment, 'active' => true],
            ['category' => 'ASC', 'name' => 'ASC'],
        );

        return $this->json(array_map($this->normalizer->dish(...), $dishes));
    }
}
