<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SortieController extends AbstractController

{
    #[Route('/', name: 'sortie_list')]
    public function list(
        SortieRepository $sortieRepository
    ): \Symfony\Component\HttpFoundation\Response
    {
        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/list.html.twig',
            [
                'sorties' => $sorties

            ]);
    }


    #[Route('/create', name: 'sortie_create')]
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