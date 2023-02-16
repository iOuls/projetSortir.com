<?php

namespace App\Controller;

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

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController

{
    #[Route('/', name: '_list')]
    public function list(
        SortieRepository $sortieRepository,
        SiteRepository   $siteRepository
    ): Response
    {
        $date = new \DateTime();
        $sorties = $sortieRepository->findAll();
        $sites = $siteRepository->findAll();
        return $this->render('sortie/list.html.twig',
            [
                'sorties' => $sorties,
                'sites' => $sites,
                'date' => $date
            ]);
    }


    #[Route('/create', name: '_create')]
    public function create(
        EntityManagerInterface $em,
        Request                $request,
        EtatRepository         $etatRepository,
        SortieRepository       $sortieRepository,

    )
    {
        $sortie = new Sortie();

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        $sortie->setOrganisateur(($this->getUser()));
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            try {

                if ($sortieForm->getClickedButton() === $sortieForm->get('Enregistrer')) {
                    $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
                } else {
                    $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
                }
            } catch (Exception $exception) {
                dd($exception->getMessage());
            }
            $sortieRepository->save($sortie, true);
            $this->addFlash('bravo', 'Sortie ajoutée.');
        }
        return $this->render('sortie/create.html.twig',
            compact('sortieForm')
        );
    }


}