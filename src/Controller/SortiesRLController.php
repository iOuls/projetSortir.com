<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SortiesRLController extends AbstractController
{

    /**
     * Se désister d'une sortie
     * @param EntityManagerInterface $em
     * @param SortieRepository $sR
     * @param Sortie $sortie
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/sorties/sedesister/{id}', name: 'sorties_desister')]
    public function desister(
        EntityManagerInterface $em,
        SortieRepository       $sR,
        Sortie                 $sortie,
        UserRepository         $userRepository,
        int                    $id
    ): Response
    {
        // vérification si User connecté
        if ($this->getUser() != null) {
            // récupération user connecté pour retrait de la sortie
            $sortie = $sR->findOneBy(['id' => $id]);
            $sortie->removeParticipant($userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]));
            try {
                // mise à jour BDD
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('Désistement effectué', 'Votre désistement a été pris en compte.');

                return $this->redirectToRoute('sortie_list');

            } catch (\Exception $e) {
                // redirection avec message KO
                $this->addFlash('Désistement non effectué', 'Vous devez être connecté pour pouvoir vous désister d\'une sortie.');
                return $this->redirectToRoute('sortie_list');
            }
        } else {
            // redirection connexion
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * Clôturer les inscriptions d'une sortie
     * @param EntityManagerInterface $em
     * @param Sortie $sortie
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/sorties/cloture/{id}', name: 'sorties_cloture')]
    public function cloture(
        EntityManagerInterface $em,
        SortieRepository       $sR,
        int                    $id
    ): Response
    {
        $sortie = $sR->findOneBy(['id' => $id]);

        if ($sortie->getOrganisateur()->getUserIdentifier() == $this->getUser()->getUserIdentifier() ||
            in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $sortie->setDateLimitInscription(new \Datetime());
            try {
                $em->persist($sortie);
                $em->flush();
            } catch (\Exception $e) {
                // redirection avec message KO
                $this->addFlash('Clôture non effectué', 'Détail de l\'erreur : ' . $e->getMessage());
                return $this->redirectToRoute('sortie_list');
            }
        } else {
            // redirection user != organisateur
            $this->addFlash('Clôture non effectué', 'Vous n\'êtes pas l\'organisateur de l\'évènement.');
            return $this->redirectToRoute('sortie_list');
        }
        $this->addFlash('Clôture effectuée', 'La clôture de l\'évènement a été enregistrée.');
        return $this->redirect('/sorties/' . $sortie->getId());
    }

    /**
     * Annuler une sortie
     * @param EntityManagerInterface $em
     * @param EtatRepository $eR
     * @param Sortie $sortie
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/sorties/annuler/{id}', name: 'sorties_annuler')]
    public function annuler(
        EntityManagerInterface $em,
        EtatRepository         $eR,
        SortieRepository       $sR,
        int                    $id
    ): Response
    {
        $sortie = $sR->findOneBy(['id' => $id]);

        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            $sortie->setEtat($eR->findOneBy(['libelle' => 'Annulée']));
            $sortie->setMotifAnnulation('Annulée par l\'administrateur.');
            try {
                $em->persist($sortie);
                $em->flush();
            } catch (\Exception $e) {
                // redirection avec message KO
                $this->addFlash('Annulation non effectué', 'Détail de l\'erreur : ' . $e->getMessage());
                return $this->redirectToRoute('/sorties/' . $sortie->getId());
            }
        } else {
            // redirection user != organisateur
            $this->addFlash('Annulation non effectué', 'Vous n\'êtes pas l\'organisateur de l\'évènement.');
            return $this->redirectToRoute('sortie_list');
        }
        $this->addFlash('Annulation effectuée', 'L\'annulation de l\'évènement a été enregistrée.');
        return $this->redirect('/sorties/' . $sortie->getId());
    }

    /**
     * S'inscrire à une sortie
     * @param int $id
     * @param SortieRepository $sortieRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/sinscrire/{id}', name: 'sorties_sinscrire')]
    public function sinscrire(
        int                    $id,
        SortieRepository       $sortieRepository,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $sortie = $sortieRepository->findOneBy(['id' => $id]);
        $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $sortie->addParticipant($user);
        try {
            $entityManager->persist($sortie);
            $entityManager->flush();
        } catch (\Exception $e) {
            // redirection avec message KO
            $this->addFlash('Inscription non effectué', 'Détail de l\'erreur : ' . $e->getMessage());
            return $this->redirectToRoute('sortie_list');
        }
        $this->addFlash('Inscription effectué', 'Vous avez été inscrit à la sortie : ' . $sortie->getNom() . '.');
        return $this->redirectToRoute('sortie_list');
    }
}
