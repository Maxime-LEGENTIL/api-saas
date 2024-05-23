<?php

namespace App\Controller;

use App\Entity\Customer;
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

class CustomerController extends AbstractController
{
    #[Route('/api/customers', name: 'app_customer_index', methods: 'GET')]
    public function index(CustomerRepository $customerRepository): JsonResponse
    {
        $customers = $customerRepository->findAll();

        return $this->json($customers, Response::HTTP_OK, [], ["groups" => "customers_list"]);
    }

    #[Route('/api/customers/{id}', name: 'app_customer_show', methods: 'GET')]
    public function show(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);
        if($customer) {
            return $this->json($customer, Response::HTTP_OK, [], ["groups" => "customers_list"]);
        }
        else {
            return $this->json(['success' => false, 'message' => "Le client indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers_list"]);
        }
    }

    #[Route('/api/customers', name: 'app_customer_post', methods: ['POST'])]
    public function post(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        try {
            $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

            //dd($request->getContent(), $customer);
            
            $errors = $validator->validate($customer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers_post"]);
            }
            
            $entityManager->persist($customer);
            $entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => "L'entité vient d'être ajoutée."
            ], Response::HTTP_CREATED, [], ["groups" => "customers_post"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'success' => false,
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers_post"]);
        }
    }
    /*$post_data = json_decode($request->getContent(), true);
    $firstname = $post_data['firstname'];
    $lastname = $post_data['lastname'];
    $email = $post_data['email'];
    $phonenumber = $post_data['phonenumber'];
    $address = $post_data['address'];
    
    dd($firstname, $lastname, $email, $email, $phonenumber, $address);*/
                

    #[Route('/api/customers/{id}', name: 'app_customer_delete', methods: 'DELETE')]
    public function delete(int $id, OrderRepository $orderRepository, CustomerRepository $customerRepository, SerializerInterface $serializer, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $customer = $customerRepository->find($id);
        if($customer) {
            $orders = $orderRepository->findOrdersByCustomerId($id);
            foreach ($orders as $key => $value) {
                $entityManagerInterface->remove($value);
                $entityManagerInterface->flush();
            }

            $entityManagerInterface->remove($customer);
            $entityManagerInterface->flush();
            return $this->json(['success' => true, 'message' => "Le client (id ".$id.") vient d'être supprimé avec succès."], Response::HTTP_OK, [], ["groups" => "customers_list"]);
        }
        else {
            return $this->json(['success' => false, 'message' => "Le client indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers_list"]);
        }
    }

    #[Route('/api/customers/{id}', name: 'app_customer_put', methods: 'PUT')]
    public function put(int $id, Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return $this->json(['success' => false, 'message' => "Le client indiqué (id ".$id.") n'existe pas."], Response::HTTP_NOT_FOUND, [], ["groups" => "customers_list"]);
        }

        try {
            // Désérialiser les nouvelles données en utilisant les données existantes du client
            $updatedCustomer = $serializer->deserialize(
                $request->getContent(),
                Customer::class,
                'json',
                ['object_to_populate' => $customer]
            );

            // Valider l'entité mise à jour
            $errors = $validator->validate($updatedCustomer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers_list"]);
            }

            // Persist the updated customer entity
            $entityManager->persist($updatedCustomer);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => "Le client (id ".$id.") a été mis à jour avec succès."
            ], Response::HTTP_OK, [], ["groups" => "customers_list"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'success' => false,
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers_list"]);
        }
    }
}