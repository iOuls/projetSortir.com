<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AnnulerSortieType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/', name: 'sortie')]
class SortieController extends AbstractController
{
    #[Route('', name: '_list')]
    public function list(
        SortieRepository $sortieRepository,
        SiteRepository   $siteRepository,
        UserRepository   $userRepository,
        Request          $request
    ): Response
    {
        // récupération des variables communes pour le return
        $date = new \DateTime();
        $sites = $siteRepository->findAll();

        // traitement si filtres activés
        if (
            $request->query->get('site') != null ||
            $request->query->get('motsclefs') != null ||
            $request->query->get('datedebut') != null ||
            $request->query->get('datefin') != null ||
            $request->query->get('organisateur') != false ||
            $request->query->get('incrit') != false ||
            $request->query->get('noninscrit') != false ||
            $request->query->get('passees') != false
        ) {

            $site = $request->query->get('site'); // filtre ok
            $motsclefs = $request->query->get('motsclefs'); // filtre ok
            $datedebut = $request->query->get('datedebut'); // TODO filtre ko
            $datefin = $request->query->get('datefin'); // TODO filtre ko
            $organisateur = ($request->query->get('organisateur') == 'on') ? true : false; // filtre ok
            $inscrit = ($request->query->get('incrit') == 'on') ? true : false; // filtre ok
            $noninscrit = ($request->query->get('noninscrit') == 'on') ? true : false; // filtre ok
            $passees = ($request->query->get('passees') == 'on') ? true : false;
            $criteres = [
                'site' => $site,
                'motsclefs' => $motsclefs,
                'datedebut' => $datedebut,
                'datefin' => $datefin,
                'organisateur' => $organisateur,
                'inscrit' => $inscrit,
                'noninscrit' => $noninscrit,
                'passees' => $passees
            ];

            $sorties = $sortieRepository->filtreSorties(
                $site,
                $motsclefs,
                $datedebut,
                $datefin,
                $userRepository->findOneBy([
                    'id' => $this->getUser()]),
                $organisateur,
                $inscrit,
                $noninscrit,
                $passees);

            return $this->render('sortie/list.html.twig',
                [
                    'sorties' => $sorties,
                    'sites' => $sites,
                    'date' => $date,
                    'criteres' => $criteres
                ]);
        }

        // traitement si aucun filtre = findall
        $sorties = $sortieRepository->findAll();
        $criteres = [
            'site' => null,
            'motsclefs' => null,
            'datedebut' => null,
            'datefin' => null,
            'organisateur' => null,
            'inscrit' => null,
            'noninscrit' => null,
            'passees' => null
        ];

        return $this->render('sortie/list.html.twig',
            [
                'sorties' => $sorties,
                'sites' => $sites,
                'date' => $date,
                'criteres' => $criteres
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