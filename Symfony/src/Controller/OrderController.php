<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class OrderController extends AbstractController
{
    /**
     * Récupère la dernière commande.
     *
     * Récupère la dernière commande de la BDD.
     *
     */
    #[Route('/api/orders/last', name: 'app_order_last', methods: 'GET')]
    #[OA\Tag(name: 'Commandes')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la dernière commande.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'order', ref: new Model(type: Order::class, groups: ['orders:read']))
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(property: 'errors', type: 'string', example: 'Error details here')
            ]
        )
    )]
    public function last(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findLatestOrder();

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders:read"]);
    }

    /**
     * Récupère la liste des commandes.
     *
     * Récupère la liste de tous les commandes de la BDD.
     *
     */
    #[Route('/api/orders', name: 'app_order_index', methods: 'GET')]
    #[OA\Tag(name: 'Commandes')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des commandes.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'order', ref: new Model(type: Order::class, groups: ['orders:read']))
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(property: 'errors', type: 'string', example: 'Error details here')
            ]
        )
    )]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findAll();

        return $this->json($orders, Response::HTTP_OK, [], ["groups" => "orders:read"]);
    }

    /**
     * Récupère les informations d'une seule commande.
     *
     * Récupère les informations d'une commande en fonction de son ID en BDD.
     *
     */
    #[Route('/api/orders/{id}', name: 'app_order_show', methods: 'GET')]
    #[OA\Tag(name: 'Commandes')]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'id de la commande à récupérer.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Retourne les informations de la commande.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'order', ref: new Model(type: Order::class, groups: ['orders:read']))
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(property: 'errors', type: 'string', example: 'Error details here')
            ]
        )
    )]
    public function show(int $id, OrderRepository $orderRepository): JsonResponse
    {
        $order = $orderRepository->find($id);
        if($order) {
            return $this->json($order, Response::HTTP_OK, [], ["groups" => "orders:read"]);
        }
        else {
            return $this->json(['message' => "Le commande indiquée (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:read"]);
        }
    }

    /**
     * Ajoute une nouvelle commande.
     *
     * Ajoute une nouvelle commande en BDD.
     *
     */
    #[Route('/api/orders', name: 'app_order_post', methods: 'POST')]
    #[OA\Tag(name: 'Commandes')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['orderNumber'],
            properties: [
                new OA\Property(property: 'orderNumber', type: 'integer', example: 5654413),
                new OA\Property(property: 'totalAmount', type: 'integer', example: 99),
                new OA\Property(
                    property: 'products',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'Le nom du produit'),
                            new OA\Property(property: 'price', type: 'integer', example: 500)
                        ]
                    )
                ),
                new OA\Property(
                    property: 'customer',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Retourne la commande ajoutée.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité vient d'être ajoutée."),
                new OA\Property(property: 'order', ref: new Model(type: Order::class, groups: ['orders:post']))
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(property: 'errors', type: 'string', example: 'Error details here')
            ]
        )
    )]
    public function post(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, CustomerRepository $customerRepository, ProductRepository $productRepository): JsonResponse
    {
        try {
            // Désérialiser la commande
            $data = json_decode($request->getContent(), true);

            //dd($data['name']);

            // Créer une nouvelle instance de commande et définir les propriétés
            $order = new Order();
            $order->setOrderNumber($data['orderNumber']);
            $order->setTotalAmount($data['totalAmount']);
            $order->setName($data['name']);

            // Récupérer le client existant à partir du JSON
            $customerId = $data['customer']['id'] ?? null;
            if (!$customerId) {
                return $this->json([
                    'message' => 'Customer ID is required'
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
            }

            $customer = $customerRepository->find($customerId);
            if (!$customer) {
                return $this->json([
                    'message' => 'Customer not found'
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
            }

            $order->setCustomer($customer);

            /// Associer les produits existants à la commande avec leur quantité
            //dd($data['products']);
            foreach ($data['products'] as $productData) {
                $productId = $productData['id'] ?? null;
                $quantity = $productData['quantity']; // Par défaut, la quantité est 1

                if (!$productId) {
                    return $this->json([
                        'message' => 'Product ID is required'
                    ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
                }

                $product = $productRepository->find($productId);
                if (!$product) {
                    return $this->json([
                        'message' => 'Product not found'
                    ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
                }

                $orderProduct = new OrderProduct();
                $orderProduct->setProduct($product);
                $orderProduct->setQuantity($quantity);
                $order->addOrderProduct($orderProduct);
            }

            // Valider l'entité
            $errors = $validator->validate($order);
            if (count($errors) > 0) {
                return $this->json([
                    'message' => 'Validation errors',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
            }

            // Persister la commande
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->json([
                'message' => "L'entité vient d'être ajoutée.",
                'order' => $order
            ], Response::HTTP_CREATED, [], ["groups" => "orders:create"]);

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
        }
    }


    /**
     * Supprime une commande.
     *
     * Supprime une commande en fonction de son ID en BDD.
     *
     */
    #[Route('/api/orders/{id}', name: 'app_order_delete', methods: 'DELETE')]
    #[OA\Tag(name: 'Commandes')]
    #[OA\Response(
        response: 200,
        description: 'La commande a été supprimé.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité vient d'être supprimé avec succès.")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(property: 'errors', type: 'string', example: 'Error details here')
            ]
        )
    )]
    public function delete(int $id, Request $request, OrderRepository $orderRepository, SerializerInterface $serializer, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $order = $orderRepository->find($id);
        if($order) {
            $products = $order->getOrderProducts();

            for ($i=0; $i < count($products); $i++) { 
                $orderProduct = $products[$i];
                $order->removeOrderProduct($orderProduct);
                //dump($products[$i]);
            }
            //dd($products);

            $entityManagerInterface->remove($order);
            $entityManagerInterface->flush();
            return $this->json(['message' => "La commande (id ".$id.") d'être supprimé avec succès."], Response::HTTP_OK, [], ["" => ""]);
        }
        else {
            return $this->json(['message' => "La commande indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["" => ""]);
        }
    }

    /**
     * Modifie une commande.
     *
     * Modifie une commande en BDD.
     *
     */
    #[Route('/api/orders/{id}', name: 'app_order_put', methods: 'PUT')]
    #[OA\Tag(name: 'Commandes')]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'id de la commande à mettre à jour.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['orderNumber', 'lastname', 'email', 'phonenumber', 'address'],
            properties: [
                new OA\Property(property: 'orderNumber', type: 'integer', example: '20000'),
                new OA\Property(property: 'totalAmount', type: 'integer', example: '99'),
                new OA\Property(
                    property: 'products',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: "Le nom du produit")
                    ]
                ),
                new OA\Property(
                    property: 'customer',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1)
                    ]
                )
            ],
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Retourne la commande modifiée.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité client vient d'être mise à jour."),
                new OA\Property(property: 'order', ref: new Model(type: Order::class, groups: ['orders:post']))
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid input',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Validation errors'),
                new OA\Property(property: 'errors', type: 'string', example: 'Error details here')
            ]
        )
    )]
    public function put(
        int $id,
        Request $request,
        OrderRepository $orderRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        CustomerRepository $customerRepository
    ): JsonResponse {
        $order = $orderRepository->find($id);
    
        if (!$order) {
            return $this->json(['message' => "La commande indiquée (id ".$id.") n'existe pas."], Response::HTTP_NOT_FOUND, [], ["groups" => ""]);
        }
    
        try {
            $data = json_decode($request->getContent(), true);
    
            // Mettre à jour les propriétés de la commande
            $order->setOrderNumber($data['orderNumber']);
            $order->setTotalAmount($data['totalAmount']);
            $order->setName($data['name']);
    
            // Récupérer le client existant à partir du JSON
            $customerId = $data['customer']['id'] ?? null;
            if (!$customerId) {
                return $this->json([
                    'message' => 'Customer ID is required'
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
            }
    
            $customer = $customerRepository->find($customerId);
            if (!$customer) {
                return $this->json([
                    'message' => 'Customer not found'
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
            }
    
            $order->setCustomer($customer);
    
            // Mettre à jour les produits associés
            $existingOrderProducts = $order->getOrderProducts();
            $newProductIds = array_map(fn($productData) => $productData['id'], $data['products']);
    
            // Supprimer les produits qui ne sont pas dans la nouvelle liste
            foreach ($existingOrderProducts as $existingOrderProduct) {
                if (!in_array($existingOrderProduct->getProduct()->getId(), $newProductIds)) {
                    $order->removeOrderProduct($existingOrderProduct);
                    $entityManager->remove($existingOrderProduct);
                }
            }
    
            // Ajouter ou mettre à jour les produits dans la commande
            foreach ($data['products'] as $productData) {
                $productId = $productData['id'] ?? null;
                $quantity = $productData['quantity'] ?? 1;
    
                if (!$productId) {
                    return $this->json([
                        'message' => 'Product ID is required'
                    ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
                }
    
                $product = $productRepository->find($productId);
                if (!$product) {
                    return $this->json([
                        'message' => 'Product not found'
                    ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:create"]);
                }
    
                $orderProduct = $existingOrderProducts->filter(
                    fn($op) => $op->getProduct()->getId() === $productId
                )->first();
    
                if (!$orderProduct) {
                    $orderProduct = new OrderProduct();
                    $orderProduct->setProduct($product);
                    $orderProduct->setOrder($order);
                    $order->addOrderProduct($orderProduct);
                }
    
                $orderProduct->setQuantity($quantity);
            }
    
            // Valider l'entité mise à jour
            $errors = $validator->validate($order);
            if (count($errors) > 0) {
                return $this->json([
                    'message' => 'Validation errors',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "orders:read"]);
            }
    
            // Persister les changements
            $entityManager->persist($order);
            $entityManager->flush();
    
            return $this->json([
                'message' => "La commande (id ".$id.") a été mise à jour avec succès.",
                'order' => $order
            ], Response::HTTP_OK, [], ["groups" => "orders:read"]);
    
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => ""]);
        }
    }    
}