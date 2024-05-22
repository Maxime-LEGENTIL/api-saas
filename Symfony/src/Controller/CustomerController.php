<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{
    #[Route('/api/customers', name: 'app_customer_index', methods: 'GET')]
    public function index(CustomerRepository $customerRepository): JsonResponse
    {
        $customers = $customerRepository->findAll();

        return $this->json($customers, Response::HTTP_OK, [], ["groups" => "customers_list"]);
    }

    #[Route('/api/customers', name: 'app_customer_post', methods: 'POST')]
    public function post(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');
        
        $entityManagerInterface->persist($customer);
        $entityManagerInterface->flush();
        
        return $this->json(
            [
                'success' => true,
                'message' => "L'entité vient d'être ajoutée."
            ], 
            Response::HTTP_OK, [], ["groups" => "customers_post"]);
            
        /*$post_data = json_decode($request->getContent(), true);
        $firstname = $post_data['firstname'];
        $lastname = $post_data['lastname'];
        $email = $post_data['email'];
        $phonenumber = $post_data['phonenumber'];
        $address = $post_data['address'];

        dd($firstname, $lastname, $email, $email, $phonenumber, $address);*/
    }
}
