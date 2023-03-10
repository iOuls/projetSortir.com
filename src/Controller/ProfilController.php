<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfilFormType;
use App\Repository\UserRepository;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
//    #[Route('/profil', name: 'app_profil')]
//    public function index(): Response
//    {
//        return $this->render('profil/index.html.twig', [
//            'controller_name' => 'ProfilController',
//        ]);
//    }

    /**
     * Recuperation et modifcation d'un profil
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @return Response
     */
    #[Route('/profil', name: 'profil_users')]
    public function users(
        Request                     $request,
        EntityManagerInterface      $em,
        UserRepository              $userRepository,
        UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = new User();

        if ($this->getUser()) {
            $user = $this->getUser();
        }

        $form = $this->createForm(ProfilFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $userRepository->findOneBy(
                ['email' => $this->getUser()->getUserIdentifier()]);

            $user->setPseudo($form->getData()->getPseudo());
            $user->setNom($form->getData()->getNom());
            $user->setPrenom($form->getData()->getPrenom());
            $user->setTelephone($form->getData()->getTelephone());
            $user->setActif($form->getData()->isActif());
            ($form->getData()->getImageFile() != null) ? $user->setImageFile($form->getData()->getImageFile()) : null;
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getPseudo() . ' a ??t?? mis ?? jour !');
        }


        return $this->render('profil/index.html.twig', [
            'profilForm' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/sonprofil/{id}', name: 'profil_afficher')]
    public function afficherProfil(
        UserRepository $userRepository,
        int            $id
    ): Response
    {
        $user = new User();
        $user = $userRepository->findOneBy(['id' => $id]);

        return $this->render('profil/participant.html.twig', [
            'user' => $user
        ]);
    }
}
