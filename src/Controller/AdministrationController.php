<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\ImportUsersType;
use App\Form\SiteType;
use App\Form\VilleType;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager,
        int                    $id
    ): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $userRepository->remove($user);
        $entityManager->flush();
        $this->addFlash('Utilisateur supprimé', $user->getPseudo() . ' a été supprimé de la liste des utilisateurs.');
        return $this->redirectToRoute('administration_listeUsers');
    }

    #[Route('/importUsers/import', name: '_importUsers')]
    public function importUsers(
        EntityManagerInterface $entityManager,
        Request                $request,
        SiteRepository         $siteRepository
    ): Response
    {
        $sites = $siteRepository->findAll();
        $usersForm = $this->createForm(ImportUsersType::class)->handleRequest($request);
        if ($usersForm->isSubmitted()) {
            $csv = $usersForm->get('csv')->getData();

            if ($csv) {
                $donnees = IOFactory::load($csv)->getActiveSheet()->toArray(null, true, true, true);
            }

            foreach ($donnees as $donnee) {
                $user = new User();
                $user->setEmail($donnee['A']);
                $user->setPassword($donnee['B']);
                $user->setNom($donnee['C']);
                $user->setPrenom($donnee['D']);
                $user->setTelephone($donnee['E']);
                $user->setAdministrateur($donnee['F']);
                $user->setActif($donnee['G']);
                $user->setPseudo($donnee['H']);
                $user->setSite($siteRepository->find($donnee['I']));

                $entityManager->persist($user);
            }

            $entityManager->flush();

            $this->addFlash('Enregistrement effectué', 'La liste des utilisateurs a été importée.');
            return $this->redirectToRoute('administration_listeUsers');
        }

        return $this->render('administration/importUsers.html.twig', [
            'userForm' => $usersForm,
            'sites' => $sites
        ]);
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

    #[Route('/gererSites/', name: '_gererSites')]
    public function gererSites(
        Request        $request,
        SiteRepository $siteRepository
    ): Response
    {
        $site = new Site();
        $siteForm = $this->createForm(SiteType::class, $site);
        $siteForm->handleRequest($request);
        $motsclefs = $request->query->get('motsclefs');
        $sites = $siteRepository->findAll();

        if ($motsclefs != null) {
            $sites = $siteRepository->findByLettre($motsclefs);
        }


        if ($siteForm->isSubmitted() && $siteForm->isValid()) {
            $siteForm = $this->createForm(SiteType::class, $site);
            $siteForm->handleRequest($request);
            $siteRepository->save($site, true);
            return $this->redirectToRoute('administration_gererSites', []);

        } else {
        $this->addFlash('Gerer', 'Les valeurs que vous avez rentrées ne sont pas valides. Veuillez vérifier et réessayer.');
    }

        return $this->render('administration/gererSites.html.twig', [
            'sites' => $sites,
            'siteForm' => $siteForm,
            'motsclefs' => $motsclefs
        ]);
    }


    #[Route('/supprimerSite/{id}', name: '_supprimerSite')]
    public function supprimerSite(
        EntityManagerInterface $em,
        SiteRepository         $siteRepository,
        int                    $id
    ): Response
    {
        $site = $siteRepository->findOneBy(['id' => $id]);
        $siteRepository->remove($site);
        $em->flush();
        $this->addFlash('Site supprimé', $site->getNom() . ' a été supprimé de la liste des sites.');
        return $this->redirectToRoute('administration_gererSites');
    }

    #[Route('/gererVilles/', name: '_gererVilles')]
    public function gererVilles(
        Request         $request,
        VilleRepository $villeRepository
    ): Response
    {
        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);
        $villeForm->handleRequest($request);
        $motsclefs = $request->query->get('motsclefs');
        $villes = $villeRepository->findAll();

        if ($motsclefs != null) {
            $villes = $villeRepository->findByLettre($motsclefs);
        }

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $villeForm = $this->createForm(VilleType::class, $ville);
            $villeForm->handleRequest($request);
            $villeRepository->save($ville, true);
            return $this->redirectToRoute('administration_gererVilles', []);

        } else {
            $this->addFlash('Gerer', 'Les valeurs que vous avez rentrées ne sont pas valides. Veuillez vérifier et réessayer.');
        }

        return $this->render('administration/gererVilles.html.twig', [
            'villes' => $villes,
            'villeForm' => $villeForm,
            'motsclefs' => $motsclefs
        ]);
    }

    #[Route('/supprimerVille/{id}', name: '_supprimerVille')]
    public function supprimerVille(
        EntityManagerInterface $em,
        VilleRepository        $villeRepository,
        int                    $id
    ): Response
    {
        $ville = $villeRepository->findOneBy(['id' => $id]);
        $villeRepository->remove($ville);
        $em->flush();
        $this->addFlash('Ville supprimée', $ville->getNom() . ' a été supprimée de la liste des villes.');
        return $this->redirectToRoute('administration_gererVilles');
    }


}
