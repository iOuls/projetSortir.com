<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AnnulerSortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'sortie')]
class SortieController extends AbstractController

{
    #[Route('', name: '_list')]
    public function list(
        SortieRepository $sortieRepository,
        SiteRepository   $siteRepository
    ): Response
    {
        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('sortie/list.html.twig',
            [
                'sorties' => $sorties,
                'sites' => $sites
            ]);
    }

    #[Route('/sortie/{id}', name: '_afficher')]
    public function afficher(
        SortieRepository $sortieRepository,
        int              $id
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        return $this->render('sortie/afficher.html.twig',
            [
                'sortie' => $sortie
            ]);
    }

    #[Route('/sortie/publier/{id}', name: '_publier')]
    public function publier(
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $em,
        int                    $id
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
        try {
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('Sortie publiée', 'Sortie publiée avec succès.');
        } catch (Exception $exception) {
            $this->addFlash('Sortie non publiée', 'Détail de l\'erreur : ' . $exception->getMessage() . '.');
        }
        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/sortie/annuler/{id}', name: '_annuler')]
    public function annuler(
        SortieRepository       $sortieRepository,
        int                    $id,
        Request                $request,
        EtatRepository         $etatRepository,
        EntityManagerInterface $em
    ): Response
    {
        $sortie = new Sortie();
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $sortieForm = $this->createForm(AnnulerSortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted()) {
            try {
                // Je récupère en base de donnée l'état créé
                $etatAnn = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                if ($etatAnn) {
                    $sortie->setEtat($etatAnn);
                    if ($sortieForm->isValid()) {
                        $em->persist($sortie);
                    }
                }
            } catch (Exception $exception) {
                dd($exception->getMessage());
            }
            $em->flush();
            $this->addFlash('Sortie annulée', 'Sortie annulée avec succès.');
            return $this->redirectToRoute('sortie_list');
        }
        return $this->render('sortie/annuler.html.twig',
            compact('sortieForm', 'sortie')
        );

    }


    #[Route('/create', name: '_create')]
    public function create(
        EntityManagerInterface $em,
        Request                $request,
        EtatRepository         $etatRepository
    )
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted()) {
            try {
                // Je récupère en base de donnée l'état créé
                $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
                if ($etatCree) {
                    $sortie->setEtat($etatCree);
                    if ($sortieForm->isValid()) {
                        $em->persist($sortie);
                    }
                }
            } catch (Exception $exception) {
                dd($exception->getMessage());
            }
            $em->flush();
            $this->addFlash('bravo', 'Sortie ajoutée.');
            return $this->redirectToRoute('sortie_create');
        }
        return $this->render('sortie/create.html.twig',
            compact('sortieForm')
        );
    }


}