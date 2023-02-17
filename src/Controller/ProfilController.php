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

    #[Route('/profil', name: 'profil_users')]
    public function users(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher)
    {$userBDD = $em->getRepository(User::class)->findOneBy(['email'=>$this->getUser()->getUserIdentifier()]);
        $user = $userBDD;

        $form = $this->createForm(ProfilFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $user_new = $form->getData();

            $userBDD->setPseudo($user_new->getPseudo());
            $userBDD->setNom($user_new->getNom());
            $userBDD->setPrenom($user_new->getPrenom());
            $userBDD->setTelephone($user_new->getTelephone());
            $userBDD->setEmail($user_new->getEmail());
            $userBDD->setActif($user_new->isActif());
            $userBDD->setImageFile($user_new->getImageFile());

            $userBDD->setPassword(
                $userPasswordHasher->hashPassword(
                    $user_new,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($userBDD);
            $em->flush();
            $this->addFlash('success', 'L\'utilisateur ' . $user->getPseudo() . ' a été été mis à jour !');
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

        return $this->render('', [
            'user' => $user
        ]);
    }
}
