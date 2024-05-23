<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'app_order_index', methods: 'GET')]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findAll();

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders_list"]);
    }

    #[Route('/api/orders/{id}', name: 'app_order_show', methods: 'GET')]
    public function show(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $order = $orderRepository->find($id);
        if($order) {
            return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders_list"]);
        }
        else {
            return $this->json(['success' => false, 'message' => "Le commande indiquée (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders_list"]);
        }
    }

    /**
     * Ajouter une nouvelle commande.
     *
     * Ajoute une nouvelle commande en base de données.
     *
     * @Route("/api/orders", methods={"POST"})
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Reward::class, groups={"full"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="order",
     *     in="query",
     *     description="The field used to order rewards",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="rewards")
     * @Security(name="Bearer")
     */
    #[Route('/api/orders', name: 'app_order_post', methods: ['POST'])]
    public function post(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, CustomerRepository $customerRepository): JsonResponse
    {
        try {
            // Désérialiser la commande
            $order = $serializer->deserialize($request->getContent(), Order::class, 'json');
            
            // Valider l'entité
            $errors = $validator->validate($order);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders_post"]);
            }
            
            // Récupérer le client existant à partir du JSON
            $data = json_decode($request->getContent(), true);
            $customerId = $data['customer']['id'] ?? null;
            if (!$customerId) {
                return $this->json([
                    'success' => false,
                    'message' => 'Customer ID is required'
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders_post"]);
            }

            $customer = $customerRepository->find($customerId);
            if (!$customer) {
                return $this->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders_post"]);
            }

            // Associer le client à la commande
            $order->setCustomer($customer);
            
            // Persister la commande
            $entityManager->persist($order);
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => "L'entité vient d'être ajoutée."
            ], Response::HTTP_CREATED, [], ["groups" => "orders_post"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'success' => false,
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders_post"]);
        }
    }

    #[Route('/api/orders/{id}', name: 'app_order_delete', methods: 'DELETE')]
    public function delete(int $id, Request $request, OrderRepository $orderRepository, SerializerInterface $serializer, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $oder = $orderRepository->find($id);
        if($oder) {
            $entityManagerInterface->remove($oder);
            $entityManagerInterface->flush();
            return $this->json(['success' => true, 'message' => "La commande (id ".$id.") d'être supprimé avec succès."], Response::HTTP_OK, [], ["" => ""]);
        }
        else {
            return $this->json(['success' => false, 'message' => "La commande indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["" => ""]);
        }
    }

    #[Route('/api/orders/{id}', name: 'app_order_put', methods: 'PUT')]
    public function put(int $id, Request $request, OrderRepository $orderRepository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            return $this->json(['success' => false, 'message' => "La commande indiquée (id ".$id.") n'existe pas."], Response::HTTP_NOT_FOUND, [], ["" => ""]);
        }

        try {
            // Désérialiser les nouvelles données en utilisant les données existantes du client
            $updatedOrder = $serializer->deserialize(
                $request->getContent(),
                Order::class,
                'json',
                ['object_to_populate' => $order]
            );

            // Valider l'entité mise à jour
            $errors = $validator->validate($updatedOrder);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders_list"]);
            }

            // Persist the updated customer entity
            $entityManager->persist($updatedOrder);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "La commande (id ".$id.") a été mise à jour avec succès."
            ], Response::HTTP_OK, [], ["groups" => "orders_list"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'success' => false,
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["" => ""]);
        }
    }
}