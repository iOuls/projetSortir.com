<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Filtre;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use http\Client\Curl\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SortieController extends AbstractController

{
    #[Route('/', name: 'sortie_list')]
    public function list(
        EntityManagerInterface $em,
        Request                $request,
        SortieRepository $sortieRepository
    ): \Symfony\Component\HttpFoundation\Response
    {
        $filtre = new Filtre();
        $sorties = $sortieRepository->findAll();

        $filtreForm =$this->createForm(FiltreType::class, $filtre);
        $filtreForm->handleRequest($request);
        if ($filtreForm->isSubmitted() && $filtreForm->isValid())
            $em->persist($filtre);
        $em->flush();

        return $this->render('sortie/list.html.twig',
            compact( 'filtreForm', 'sorties')
        );
    }


    #[Route('/create', name: 'sortie_create')]
    public function create(
        EntityManagerInterface $em,
        Request                $request,
        EtatRepository         $etatRepository,
        UserRepository         $userRepository

    )
    {
        $sortie = new Sortie();
        $date = new \DateTime();


        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $sortie->setOrganisateur(($this->getUser()));
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