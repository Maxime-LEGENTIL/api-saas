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
    #[Route('/api/products', name: 'app_product_index', methods: 'GET')]
    #[OA\Tag(name: 'Produits')]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        return $this->json($products, Response::HTTP_OK, [], ["groups" => "products_list"]);
    }

    #[Route('/api/products/{id}', name: 'app_product_show', methods: 'GET')]
    #[OA\Tag(name: 'Produits')]
    public function show(int $id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        if($product) {
            return $this->json($product, Response::HTTP_OK, [], ["groups" => "products_list"]);
        }
        else {
            return $this->json(['success' => false, 'message' => "Le produit indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "products_list"]);
        }
    }

    #[Route('/api/products', name: 'app_product_post', methods: ['POST'])]
    #[OA\Tag(name: 'Produits')]
    public function post(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        try {
            $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

            //dd($request->getContent(), $product);
            
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products_post"]);
            }
            
            $entityManager->persist($product);
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => "L'entité vient d'être ajoutée."
            ], Response::HTTP_CREATED, [], ["groups" => "products_post"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'success' => false,
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products_post"]);
        }
    }           

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
            return $this->json(['success' => true, 'message' => "Le produit (id ".$id.") vient d'être supprimé avec succès."], Response::HTTP_OK, [], ["groups" => "products_list"]);
        }
        else {
            return $this->json(['success' => false, 'message' => "Le produit indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "products_list"]);
        }
    }

    #[Route('/api/products/{id}', name: 'app_product_put', methods: 'PUT')]
    #[OA\Tag(name: 'Produits')]
    public function put(int $id, Request $request, ProductRepository $productRepository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $productRepository->find($id);

        if (!$product) {
            return $this->json(['success' => false, 'message' => "Le produit indiqué (id ".$id.") n'existe pas."], Response::HTTP_NOT_FOUND, [], ["groups" => "products_list"]);
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
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products_list"]);
            }

            // Persist the updated product entity
            $entityManager->persist($updatedProduct);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "Le produit (id ".$id.") a été mis à jour avec succès."
            ], Response::HTTP_OK, [], ["groups" => "products_list"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'success' => false,
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products_list"]);
        }
    }
}