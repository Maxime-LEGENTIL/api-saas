<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Customer;
use App\Entity\Email;
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

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class CustomerController extends AbstractController
{
    /**
     * Récupère la liste des clients.
     *
     * Récupère la liste de tous les clients de la BDD.
     *
     */
    #[OA\Tag(name: 'Clients')]
    #[Route('/api/customers', name: 'app_customer_index', methods: 'GET')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des clients.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'customer', ref: new Model(type: Customer::class, groups: ['customers:read']))
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
    public function index(CustomerRepository $customerRepository): JsonResponse
    {
        $customers = $customerRepository->findAll();

        return $this->json($customers, Response::HTTP_OK, [], ["groups" => "customers:read"]);
    }

    /**
     * Récupère les informations d'un seul client.
     *
     * Récupère les informations d'un client en fonction de son ID en BDD.
     *
     */
    #[Route('/api/customers/{id}', name: 'app_customer_show', methods: 'GET')]
    #[OA\Tag(name: 'Clients')]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'id du client à récupérer.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Retourne les informations du client.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'customer', ref: new Model(type: Customer::class, groups: ['customers:post']))
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
    public function show(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);
        if($customer) {
            return $this->json($customer, Response::HTTP_OK, [], ["groups" => "customers:read"]);
        }
        else {
            return $this->json(['message' => "Le client indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers:read"]);
        }
    }

    /**
     * Ajoute un nouveau client.
     *
     * Ajoute un nouveau client en BDD.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    #[Route('/api/customers', name: 'app_customer_post', methods: ['POST'])]
    #[OA\Tag(name: 'Clients')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['firstname', 'lastname', 'email', 'phonenumber', 'address'],
            properties: [
                new OA\Property(property: 'firstname', type: 'string', example: 'John'),
                new OA\Property(property: 'lastname', type: 'string', example: 'Doe'),
                new OA\Property(property: 'email', type: 'string', example: 'john.doe@example.com'),
                new OA\Property(property: 'phonenumber', type: 'string', example: '1234567890'),
                new OA\Property(
                    property: 'address',
                    type: 'object',
                    required: ['country', 'city', 'zipcode'],
                    properties: [
                        new OA\Property(property: 'country', type: 'string', example: 'France'),
                        new OA\Property(property: 'city', type: 'string', example: 'Paris'),
                        new OA\Property(property: 'zipcode', type: 'string', example: '75001')
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Retourne le client ajouté.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité vient d'être ajoutée."),
                new OA\Property(property: 'customer', ref: new Model(type: Customer::class, groups: ['customers:post']))
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
            //dd($request->getContent());
            //$customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
            $json_to_array = json_decode($request->getContent(), true);
            //dd($json_to_array);
            
            // On extrait les données
            $address = $json_to_array['address'];
            $country = $json_to_array['country'];
            $city = $json_to_array['city'];
            $zipcode = $json_to_array['zipcode'];
            $firstname = $json_to_array['firstname'];
            $lastname = $json_to_array['lastname'];
            $email = $json_to_array['email'];
            $phonenumber = $json_to_array['phonenumber'];

            // Customer :
            $customer = new Customer();
            $customer->setFirstname($firstname);
            $customer->setLastname($lastname);
            $customer->setPhonenumber($phonenumber);

            $entityManager->persist($customer);
            $entityManager->flush(); // Flushing here to get the ID of the customer


            /*$errors = $validator->validate($customer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers:post"]);
            }*/

            // Addresse :
            $newAddress = new Address();
            $newAddress->setCountry($country);
            $newAddress->setCity($city);
            $newAddress->setZipcode($zipcode);
            $newAddress->setAddress($address);
            $newAddress->addCustomer($customer);

            $entityManager->persist($newAddress);
            $entityManager->flush();

            // Email :
            $newEmail = new Email();
            $newEmail->setEmail($email);
            $newEmail->setCustomer($customer);

            $entityManager->persist($newEmail);
            $entityManager->flush();

            return $this->json([
                'message' => "L'entité vient d'être ajoutée.",
                'customer' => $customer
            ], Response::HTTP_CREATED, [], ["groups" => "customers:post"]);

        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers:post"]);
        }
    }           

    /**
     * Supprime un client.
     *
     * Supprime un client en fonction de son ID en BDD.
     *
     */
    #[Route('/api/customers/{id}', name: 'app_customer_delete', methods: 'DELETE')]
    #[OA\Tag(name: 'Clients')]
    #[OA\Response(
        response: 200,
        description: 'Le client a été supprimé.',
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
            return $this->json(['message' => "Le client (id ".$id.") vient d'être supprimé avec succès."], Response::HTTP_OK, [], ["groups" => "customers:read"]);
        }
        else {
            return $this->json(['message' => "Le client indiqué (id ".$id.") n'existe pas."], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers:read"]);
        }
    }

    /**
     * Modifie un client.
     *
     * Modifie un client en BDD.
     *
     */
    #[Route('/api/customers/{id}', name: 'app_customer_put', methods: 'PUT')]
    #[OA\Tag(name: 'Clients')]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'id du client à mettre à jour.',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['firstname', 'lastname', 'email', 'phonenumber', 'address'],
            properties: [
                new OA\Property(property: 'firstname', type: 'string', example: 'John'),
                new OA\Property(property: 'lastname', type: 'string', example: 'Doe'),
                new OA\Property(property: 'email', type: 'string', example: 'john.doe@example.com'),
                new OA\Property(property: 'phonenumber', type: 'string', example: '1234567890'),
                new OA\Property(property: 'address', type: 'string', example: '123 Main St')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Retourne le client modifié.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', example: "L'entité client vient d'être mise à jour."),
                new OA\Property(property: 'customer', ref: new Model(type: Customer::class, groups: ['customers:post']))
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
    public function put(int $id, Request $request, CustomerRepository $customerRepository, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return $this->json(['message' => "Le client indiqué (id ".$id.") n'existe pas."], Response::HTTP_NOT_FOUND, [], ["groups" => "customers:read"]);
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
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers:read"]);
            }

            // Persist the updated customer entity
            $entityManager->persist($updatedCustomer);
            $entityManager->flush();

            return $this->json([
                'message' => "Le client (id ".$id.") a été mis à jour avec succès."
            ], Response::HTTP_OK, [], ["groups" => "customers:read"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "customers:read"]);
        }
    }
}