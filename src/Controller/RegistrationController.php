<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function PHPUnit\Framework\assertFalse;

class RegistrationController extends AbstractController
{
    /**
     * Creation d'un profil + add flash regex
     * @param Request $request
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param UserAuthenticatorInterface $userAuthenticator
     * @param AppAuthenticator $authenticator
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface  $userAuthenticator,
        AppAuthenticator            $authenticator,
        EntityManagerInterface      $entityManager
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $user_new = new User();
                $user_new->setPseudo($user->getPseudo());
                $user_new->setNom($user->getNom());
                $user_new->setPrenom($user->getPrenom());
                $user_new->setTelephone($user->getTelephone());
                $user_new->setEmail($user->getEmail());
                $user_new->setAdministrateur(false);
                $user_new->setActif(true);
                $user_new->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $user_new->setSite($user->getSite());

                $entityManager->persist($user_new);
                $entityManager->flush();
                // do anything else you need here, like send an email
                if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                    return $this->redirectToRoute('administration_listeUsers');
                }

                return $userAuthenticator->authenticateUser(
                    $user_new,
                    $authenticator,
                    $request
                );

            } else {
                $this->addFlash('Register', 'Les valeurs que vous avez rentr??es ne sont pas valides. Veuillez v??rifier et r??essayer.');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
