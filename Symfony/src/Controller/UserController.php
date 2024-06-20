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
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
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
                    // Optionnel : Créez un nouvel utilisateur si non existant
                    $user = new User();
                    $user->setEmail($email);
                    $user->setFirstname($firstname);
                    $user->setLastname($lastname);
                    $user->setPassword('Google account');
                    $user->setCreatedAt(new DateTimeImmutable());
                    // Vous pouvez ajouter plus d'informations ici
                    $entityManager->persist($user);
                    $entityManager->flush();
                    // TODO HERE
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
