<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController

{
    #[Route('/list', name: '_list')]
    public function list(
        SortieRepository $sortieRepository
    ): \Symfony\Component\HttpFoundation\Response
    {
        $sorties = $sortieRepository->findAll();
        return $this->render('sortie/list.html.twig',
            [
                "sorties" => $sorties

            ]);
    }


    #[Route('/create', name: '_create')]
    public function create(
        EntityManagerInterface $em,
        Request                $request
    )
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted()) {
            try {

                if ($sortieForm->isValid()) {
                    $em->persist($sortie);
                }
            } catch (Exception $exception) {
                dd($exception->getMessage());
            }
            $em->flush();
            $this->addFlash('bravo', 'Sortie ajouter.');
            return $this->redirectToRoute('sortie_create');
        }
        return $this->render('sortie/create.html.twig',
            compact('sortieForm')
        );
    }



}