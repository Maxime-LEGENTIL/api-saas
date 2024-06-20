<?php
namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client as GoogleClient;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface as SerializerSerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/users/create', name: 'app_user_create', methods: 'POST')]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerSerializerInterface $serializerInterface, ValidatorInterface $validator)
    {
        try {
            $user = $serializerInterface->deserialize($request->getContent(), User::class, 'json');
            //dd($user);
            
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return $this->json([
                    'message' => 'Validation errors',
                    'errors' => $errorsString
                ], Response::HTTP_BAD_REQUEST, [], ["groups" => "users:create"]);
            }
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            return $this->json([
                'message' => "L'entité vient d'être ajoutée.",
                'user' => $user
            ], Response::HTTP_CREATED, [], ["groups" => "users:create"]);
            
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'message' => "Invalid JSON",
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST, [], ["groups" => "products:create"]);
        }
    }


    #[Route('/connect/google/check', name: 'connect_google_check', methods: 'POST')]
    public function connectCheckAction(Request $request, EntityManagerInterface $entityManager, JWTTokenManagerInterface $JWTManager)
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            return new JsonResponse(['error' => 'Token non fourni'], 400);
        }

        // Configuration du client Google
        $client = new GoogleClient(['client_id' => '550092839328-ke9plfaaasoljdavoqa9ond0bu888f98.apps.googleusercontent.com']); // Spécifiez votre client_id ici

        try {
            // Valide le token et récupère les informations de l'utilisateur
            $payload = $client->verifyIdToken($token);

            if ($payload) {
                //dd($payload);
                $email = $payload['email'];
                $firstname = $payload['given_name'];
                $lastname = $payload['family_name'];

                // Vérifiez si l'utilisateur existe déjà dans la base de données
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user) {
                    return new JsonResponse(['error' => 'Email inconnue'], 400);
                }

                // Générez un JWT pour l'utilisateur
                $jwt = $JWTManager->create($user);

                return new JsonResponse(['token' => $jwt]);
            } else {
                return new JsonResponse(['error' => 'Jeton invalide'], 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
