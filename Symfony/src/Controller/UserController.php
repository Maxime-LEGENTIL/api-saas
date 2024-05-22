<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'app_user_index', methods: 'GET')]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        return $this->json($users, Response::HTTP_OK, [], ["groups" => "users_list"]);
    }
}