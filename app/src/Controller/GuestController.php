<?php

namespace App\Controller;

use App\Entity\Guest;
use App\Repository\GuestRepository;
use App\Service\GuestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GuestController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private GuestService $guestService;
    private GuestRepository $guestRepository;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, GuestService $guestService, GuestRepository $guestRepository)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->guestService = $guestService;
        $this->guestRepository = $guestRepository;
    }

    #[Route('/api/guest/{id}', name: 'app_guest_show_one', methods: ['GET'])]
    public function showOne(int $id): JsonResponse
    {
        $guest = $this->guestRepository->find($id);
        return $this->json([
            'guest' => $guest
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/guests', name: 'app_guest_show_all', methods: ['GET'])]
    public function showAll(): JsonResponse
    {
        $guests = $this->guestRepository->findAll();
        return $this->json([
            'guests' => $guests
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/guests', name: 'app_guest_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $guest = $this->serializer->deserialize($request->getContent(), Guest::class, 'json');

        $errors = $this->guestService->validateGuest($guest);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        try {
            $countryByPhone = $this->guestService->determineCountryByPhone($guest->getPhone());
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if (empty($guest->getCountry())) {
            $guest->setCountry($countryByPhone);
        }

        $this->entityManager->persist($guest);
        $this->entityManager->flush();

        return $this->json([
            'message' => sprintf("Guest %s created successfully" ,$guest->getFirstName()),
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/guest/{id}', name: 'app_guest_update', methods: ['PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $guest = $this->guestRepository->find($id);

        if (!$guest) {
            return $this->json(['message' => 'Guest not found'], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true);

        try {
            $this->guestService->updateGuest($requestData, $guest);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $this->validator->validate($guest);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Guest updated successfully',
            'guest' => $guest
        ]);
    }

    #[Route('/api/guest/{id}', name: 'app_guest_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $guest = $this->guestRepository->find($id);

        if (!$guest) {
            return $this->json(['message' => 'Guest not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($guest);
        $this->entityManager->flush();

        return $this->json(['message' => 'Guest deleted successfully']);
    }
}
