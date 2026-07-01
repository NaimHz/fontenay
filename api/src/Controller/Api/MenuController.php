<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\DishRepository;
use App\Service\ApiNormalizer;
use App\Service\EstablishmentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** La carte de l'établissement, pour la prise de commande. */
#[Route('/api')]
class MenuController extends AbstractController
{
    public function __construct(
        private readonly DishRepository $dishes,
        private readonly EstablishmentResolver $resolver,
        private readonly ApiNormalizer $normalizer,
    ) {
    }

    #[Route('/dishes', name: 'api_dishes', methods: ['GET'])]
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
