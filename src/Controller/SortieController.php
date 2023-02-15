<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
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