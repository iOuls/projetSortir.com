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
use Container0W0tHVL\getConsole_ErrorListenerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use http\Client\Curl\User;
use PhpParser\Node\Scalar\String_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController

{
    #[Route('/', name: '_list')]
    public function list(
        EntityManagerInterface $em,
        Request                $request,
        SortieRepository $sortieRepository
    ): \Symfony\Component\HttpFoundation\Response
    {
        $filtre = new Filtre();
        $date = new \DateTime();
        $sorties = $sortieRepository->findAll();

        $filtreForm =$this->createForm(FiltreType::class, $filtre);
        $filtreForm->handleRequest($request);
        if ($filtreForm->isSubmitted() && $filtreForm->isValid())
            $em->persist($filtre);
        $em->flush();

        return $this->render('sortie/list.html.twig',
            compact( 'filtreForm', 'sorties', 'date')
        );
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

                if($sortieForm->getClickedButton() === $sortieForm->get('Enregistrer')){
                    $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Créée']));
                }else{
                    $sortie->setEtat($etatRepository->findOneBy(['libelle'=>'Ouverte']));
                }
                // Je récupère en base de donnée l'état créé
/*0
                if($param == 'Enregistrer') {
                    $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
                    if ($etatCree) {
                        $sortie->setEtat($etatCree);
                        if ($sortieForm->isValid()) {
                            $em->persist($sortie);
                        }
                    }
                }elseif($param == 'Publier'){
                    $etatOuvert = $etatRepository->findOneBy(['libelle' => 'Ouvert']);
                    if ($etatOuvert) {
                        $sortie->setEtat($etatOuvert);
                        if ($sortieForm->isValid()) {
                            $em->persist($sortie);
                    }
                }
            }*/
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