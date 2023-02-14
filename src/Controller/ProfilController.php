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

    #[Route('/profil', name: 'app_profil')]
    public function users(Request $request, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createForm(ProfilFormType::class, $user);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user_id = $form['id']->getData();
            $user = $em->getRepository(User::class)->findOneById($user_id);


            $user_new = $form->getData();

            $user->setPseudo($user_new->getPseudo());
            $user->setNom($user_new->getNom());
            $user->setPrenom($user_new->getPrenom());
            $user->setTelephone($user_new->getTelephone());
            $user->setMail($user_new->getMail());
            $user->setCampus($user_new->getCampus());
            $user->setActif($user_new->isActif());

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getPseudo() . ' a été été mis à jour !');
        }

        $users = $em->getRepository(User::class)->findAll();

        return $this->render('profil/index.html.twig', [
            'profilForm' => $form->createView(),
            'users' => $users
        ]);
    }

    #[Route('/profil/{id}', name: 'profil_afficher')]
    public function afficherProfil(
        UserRepository $userRepository,
        int            $id
    ): Response
    {
        $user = new User();
        $user = $userRepository->findOneBy(['id' => $id]);

        return $this->render('', [
            'user' => $user
        ]);
    }
}