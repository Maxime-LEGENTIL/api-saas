<?php

namespace App\Controller;

use App\Entity\Product;
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

class ProductController extends AbstractController
{
    
    /**
     * Récupère la liste des produits.
     *
     * Récupère la liste de tous les produits de la BDD.
     *
     */
    #[Route('/api/products', name: 'app_product_index', methods: 'GET')]
    #[OA\Tag(name: 'Produits')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'product', ref: new Model(type: Product::class, groups: ['products:read']))
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
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        return $this->json($products, Response::HTTP_OK, [], ["groups" => "products:read"]);
    }

    /**
     * Récupère les informations d'un seul produit.
     *
     * Récupère les informations d'un produit en fonction de son ID en BDD.
     *
     */
    #[Route('/api/products/{id}', name: 'app_product_show', methods: 'GET')]
    #[OA\Tag(name: 'Produits')]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'id du produit à récupérer.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Retourne les informations du produit.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'product', ref: new Model(type: Product::class, groups: ['products:read']))
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
    public function show(int $id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        if($product) {
            return $this->json($product, Response::HTTP_OK, [], ["groups" => "products:read"]);
        }
        else {
            return $this->json(['message' => "Le produit indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "products:read"]);
        }
    }

    /**
     * Ajoute un nouveau produit.
     *
     * Ajoute un nouveau produit en BDD.
     *
     */
    #[Route('/api/products', name: 'app_product_post', methods: ['POST'])]
    #[OA\Tag(name: 'Produits')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['name', 'price'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Paravent'),
                new OA\Property(property: 'price', type: 'string', example: '50')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Retourne le produit ajouté.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité vient d'être ajoutée."),
                new OA\Property(property: 'product', ref: new Model(type: Product::class, groups: ['products:create']))
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
    public function post(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        try {
            $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

            //dd($request->getContent(), $product);
            
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products:create"]);
            }
            
            $entityManager->persist($product);
            $entityManager->flush();
            
            return $this->json([
                'message' => "L'entité vient d'être ajoutée.",
                'product' => $product
            ], Response::HTTP_CREATED, [], ["groups" => "products:create"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products:create"]);
        }
    }           

    /**
     * Supprime un produit.
     *
     * Supprime un produit en fonction de son ID en BDD.
     *
     */
    #[Route('/api/products/{id}', name: 'app_product_delete', methods: 'DELETE')]
    #[OA\Tag(name: 'Produits')]
    public function delete(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $product = $productRepository->find($id);
        if($product) {
            /*$orders = $orderRepository->findOrdersByCustomerId($id);
            foreach ($orders as $key => $value) {
                $entityManagerInterface->remove($value);
                $entityManagerInterface->flush();
            }*/

            $entityManagerInterface->remove($product);
            $entityManagerInterface->flush();
            return $this->json(['message' => "Le produit (id ".$id.") vient d'être supprimé avec succès."], Response::HTTP_OK, [], ["" => ""]);
        }
        else {
            return $this->json(['message' => "Le produit indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["" => ""]);
        }
    }

    /**
     * Modifie un client.
     *
     * Modifie un client en BDD.
     *
     */
    #[Route('/api/products/{id}', name: 'app_product_put', methods: 'PUT')]
    #[OA\Tag(name: 'Produits')]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'id du produit à mettre à jour.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['name', 'price'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Paravent'),
                new OA\Property(property: 'price', type: 'integer', example: '50')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Retourne le produit modifié.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité produit vient d'être mise à jour."),
                new OA\Property(property: 'product', ref: new Model(type: Product::class, groups: ['products:put']))
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
    public function put(int $id, Request $request, ProductRepository $productRepository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json(['message' => "Le produit indiqué (id ".$id.") n'existe pas."], Response::HTTP_NOT_FOUND, [], ["groups" => "products:put"]);
        }

        try {
            // Désérialiser les nouvelles données en utilisant les données existantes du produit
            $updatedProduct = $serializer->deserialize(
                $request->getContent(),
                Product::class,
                'json',
                ['object_to_populate' => $product]
            );

            // Valider l'entité mise à jour
            $errors = $validator->validate($updatedProduct);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products:put"]);
            }

            // Persist the updated product entity
            $entityManager->persist($updatedProduct);
            $entityManager->flush();

            return $this->json([
                'message' => "Le produit (id ".$id.") a été mis à jour avec succès.",
                'product' => $product
            ], Response::HTTP_OK, [], ["groups" => "products:put"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products:put"]);
        }
    }
}