<?php

namespace App\Controller;


use App\Entity\Groupe;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Form\AnnulerSortieType;
use App\Form\GroupeType;
use App\Form\LieuType;
use App\Form\ModifierSortieType;
use App\Form\SortieType;
use App\Form\UserType;
use App\Repository\EtatRepository;
use App\Repository\GroupeRepository;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use DateInterval;
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
        SortieRepository       $sortieRepository,
        SiteRepository         $siteRepository,
        UserRepository         $userRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $em,
        Request                $request
    ): Response
    {
        // récupération des éléments de traitement
        $date = new \DateTime();
        $sites = $siteRepository->findAll();
        $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
        $etatCree = $etatRepository->findOneBy(['libelle' => 'Créée']);
        $idUser = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

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

        } else {
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
        }

        foreach ($sorties as $sortie) {
            // sorties datedébut m-1 et plus
            if ($sortie->getDateHeureDebut() < date_sub($date, new DateInterval('P1M'))) {
                unset($sorties[array_search($sortie, $sorties, true)]);
            }

            // sorties != état créé & user non organisateur
            if ($sortie->getOrganisateur() != $idUser) {
                if ($sortie->getEtat() == $etatCree) {
                    unset($sorties[array_search($sortie, $sorties, true)]);
                }
            }

            // clôture sorties date inscription dépassée
            if ($date >= $sortie->getDateLimitInscription()) {
                $sortie->setEtat($etatCloturee);
                $em->persist($sortie);
                $em->flush();
            }
        }

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
        int              $id,
    ): Response
    {
        $date = new \DateTime();
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        if ($sortie->getDateHeureDebut() < date_sub($date, new DateInterval('P1M'))) {
            $this->addFlash('Sortie non consultable', 'La sortie sélectionnée est trop ancienne pour être consultée.');
            return $this->redirectToRoute('sortie_list');
        } else {
            return $this->render('sortie/afficher.html.twig',
                [
                    'sortie' => $sortie
                ]);
        }
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
        LieuRepository         $lieuRepository,
        VilleRepository        $villeRepository
    )
    {

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        $sortie->setOrganisateur($this->getUser());
        $lieux = $lieuRepository->findAll();
        $villes = $villeRepository->findAll();

        if ($sortieForm->isSubmitted()) {
            if ($sortieForm->isValid()) {
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
                return $this->redirectToRoute('sortie_list', []);
            } else {
                if ($sortie->getDateLimitInscription() > $sortie->getDateHeureDebut()) {
                    $this->addFlash('Erreur', "Oups, date d'inscription dépasse date de sortie");
                }
                $this->addFlash('Erreur', 'Veuillez ressaisir le formulaire svp');
            }
        }

        return $this->render('sortie/create.html.twig',
            compact('sortieForm', 'lieux', 'villes')
        );
    }

    #[Route('/modifier/{id}', name: '_modifier')]
    public function modifier(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
        EtatRepository         $etatRepository,
        SortieRepository       $sortieRepository,

    )
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $modifierSortieForm = $this->createForm(ModifierSortieType::class, $sortie);
        $modifierSortieForm->handleRequest($request);

        $sortie->setOrganisateur(($this->getUser()));
        if ($modifierSortieForm->isSubmitted()) {

            if ($modifierSortieForm->isValid()) {
                try {
                    if ($modifierSortieForm->getClickedButton() === $modifierSortieForm->get('Enregistrer')) {
                        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
                    } else {
                        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
                    }
                } catch (Exception $exception) {
                    dd($exception->getMessage());
                }
                $sortieRepository->save($sortie, true);
                $this->addFlash('bravo', 'Sortie ajoutée.');
                return $this->redirectToRoute('sortie_list', []);
            } else {
                if ($sortie->getDateLimitInscription() > $sortie->getDateHeureDebut()) {
                    $this->addFlash('Erreur', "Oups, La date limite d'inscription ne peut pas dépasser la date de la sortie");
                }
                $this->addFlash('Erreur', 'Veuillez ressaisir le formulaire svp');

            }

            $em->flush();
            /* $sortieRepository->save($sortie, true);*/
            $this->addFlash('bravo', 'Sortie ajoutée.');
            $em->persist($sortie);
            return $this->redirectToRoute('sortie_list', []);
        }
        return $this->render('sortie/modifier.html.twig',
            compact('modifierSortieForm')
        );

    }

    #[Route('/groupe/{id}', name: '_groupe')]
    public function groupe(
        EntityManagerInterface $em,
        Request                $request,
        SortieRepository       $sortieRepository,
        GroupeRepository       $groupeRepository,
        int                    $id
    )
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $groupe = new Groupe();
        $groupeForm = $this->createForm(GroupeType::class, $groupe);
        $groupeForm->handleRequest($request);
        $groupeExist = $groupeRepository->findOneBy(['sortie' => $sortie]);

        if ($groupeExist != null) {
            $em->persist($groupeExist);
            $em->flush();
        } elseif ($groupeForm->isSubmitted() && $groupeForm->isValid()) {
            $groupe->setSortie($sortie);
            $em->persist($groupe);
            $em->flush();

            $this->addFlash('Groupe créé', 'Groupe créé avec succès.');
            return $this->redirectToRoute('sortie_ajouterParticipant', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/groupe.html.twig',
            compact('groupeForm', 'sortie', 'groupeExist'));
    }

    #[Route('/ajouterParticipant/{id}', name: '_ajouterParticipant')]
    public function ajouterParticipant(
        EntityManagerInterface $em,
        GroupeRepository       $groupeRepository,
        SortieRepository       $sortieRepository,
        UserRepository         $userRepository,
        int                    $id,
    )
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $groupe = $groupeRepository->findOneBy(['sortie' => $sortie]);

        return $this->render('sortie/ajouterParticipant.html.twig',
            compact('groupe', 'sortie'));
    }

    #[Route('/ajouter/{id}/{idUser}/{idSortie}', name: '_ajouter')]
    public function ajouter(
        int                    $id,
        int                    $idUser,
        int                    $idSortie,
        UserRepository         $userRepository,
        GroupeRepository       $groupeRepository,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $entityManager

    )
    {
        $sortie = $sortieRepository->findOneBy(['id' => $idSortie]);
        $groupe = $groupeRepository->findOneBy(['id' => $id]);
        $user = $userRepository->findOneBy(['id' => $idUser]);

        $groupe->addParticipant($user);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('Ajout effectué', 'Participant ajouté au groupe');
        return $this->redirectToRoute('sortie_ajouterParticipant', ['id' => $sortie->getId()]);
    }

    #[Route('/exclure/{id}/{idUser}/{idSortie}', name: '_exclure')]
    public function exclure(
        int                    $id,
        int                    $idUser,
        int                    $idSortie,
        UserRepository         $userRepository,
        GroupeRepository       $groupeRepository,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $entityManager

    )
    {
        $sortie = $sortieRepository->findOneBy(['id' => $idSortie]);
        $groupe = $groupeRepository->findOneBy(['id' => $id]);
        $user = $userRepository->findOneBy(['id' => $idUser]);

        $groupe->removeParticipant($user);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('Exclusion effectuée', 'Participant exclu du groupe');
        return $this->redirectToRoute('sortie_ajouterParticipant', ['id' => $sortie->getId()]);
    }

    #[Route('/ajouterlieu', name: '_ajouterlieu')]
    public function ajouterlieu(
        EntityManagerInterface $entityManager,
        Request                $request
    )
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
        }
        return $this->render('lieu/ajouterLieu.html.twig', [
                'lieuForm' => $lieuForm
            ]
        );
    }
}