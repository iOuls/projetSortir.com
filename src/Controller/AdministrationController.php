<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/administration', name: 'administration')]
class AdministrationController extends AbstractController
{
    #[Route('', name: '_index')]
    public function index(): Response
    {
        return $this->render('administration/index.html.twig', [
            'controller_name' => 'AdministrationController',
        ]);
    }

    #[Route('/listeUsers', name: '_listeUsers')]
    public function listeUsers(
        UserRepository $userRepository
    ): Response
    {
        $listeUsers = $userRepository->findAll();

        return $this->render('administration/listeUsers.html.twig', [
            'controller_name' => 'AdministrationController',
            'listeUsers' => $listeUsers
        ]);
    }

    #[Route('/actif/{id}', name: '_actif')]
    public function actif(
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
        int                    $id
    ): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        ($user->isActif()) ? $user->setActif(false) : $user->setActif(true);

        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash('Changement effectué', 'L\'attribut actif de ' . $user->getPseudo() . ' a été modifié.');
        return $this->redirectToRoute('administration_listeUsers');
    }

    #[Route('/supprimer/{id}', name: '_supprimer')]
    public function supprimer(
        UserRepository $userRepository,
        int            $id
    ): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $userRepository->remove($user);
        $this->addFlash('Utilisateur supprimé', $user->getPseudo() . ' a été supprimé de la liste des utilisateurs.');
        return $this->redirectToRoute('administration_listeUsers');
    }

    #[Route('/importUsers', name: '_importUsers')]
    public function importUsers(): Response
    {
        //TODO : faire la méthode pour import de la liste CSV

        $this->addFlash('Enregistrement effectué', 'La liste des utilisateurs a été importée.');
        return $this->redirectToRoute('administration_listeUsers');
    }

    #[Route('/listeSorties', name: '_listeSorties')]
    public function listeSorties(
        SortieRepository $sortieRepository
    ): Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('administration/listeSorties.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/supprimerSortie/{id}', name: '_supprimerSortie')]
    public function supprimerSortie(
        SortieRepository $sortieRepository,
        int              $id
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $sortieRepository->remove($sortie);
        $this->addFlash('Sortie supprimée', $sortie->getNom() . ' a été supprimé de la liste des sorties.');
        return $this->redirectToRoute('administration_listeSorties');
    }
}
