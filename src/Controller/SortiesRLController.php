<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
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
        int                    $id
    ): Response
    {
        // vérification si User connecté
        if ($this->getUser() != null) {
            // récupération user connecté pour retrait de la sortie
            $user = new User();
            $user->setEmail($this->getUser()->getUserIdentifier());
            $sortie = $sR->findOneBy(['id' => $id]);
            $sortie->removeParticipant($user);
            try {
                // mise à jour BDD
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('Désistement effectué', 'Votre désistement a été pris en compte.');

                // TODO : vérifier la route
                return $this->redirectToRoute('sorties_list');

            } catch (\Exception $e) {
                // redirection avec message KO
                $this->addFlash('Désistement non effectué', 'Détail de l\'erreur : ' . $e->getMessage());
                // TODO : vérifier la route
                return $this->redirectToRoute('sorties_list');
            }
        } else {
            // redirection connexion
            // TODO : vérifier la route
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
                // TODO : vérifier la route
                return $this->redirectToRoute('sorties_list');
            }
        } else {
            // redirection user != organisateur
            $this->addFlash('Clôture non effectué', 'Vous n\'êtes pas l\'organisateur de l\'évènement.');
            // TODO : vérifier la route
            return $this->redirectToRoute('sorties_list');
        }


        // TODO : vérifier la route
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

        if ($sortie->getOrganisateur()->getUserIdentifier() == $this->getUser()->getUserIdentifier() ||
            in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            $sortie->setEtat($eR->findOneBy(['libelle' => 'Annulée']));
            try {
                $em->persist($sortie);
                $em->flush();
            } catch (\Exception $e) {
                // redirection avec message KO
                $this->addFlash('Annulation non effectué', 'Détail de l\'erreur : ' . $e->getMessage());
                // TODO : vérifier la route
                return $this->redirectToRoute('/sorties/' . $sortie->getId());
            }
        } else {
            // redirection user != organisateur
            $this->addFlash('Annulation non effectué', 'Vous n\'êtes pas l\'organisateur de l\'évènement.');
            // TODO : vérifier la route
            return $this->redirectToRoute('sorties_list');
        }

        // TODO : vérifier la route
        return $this->redirect('/sorties/' . $sortie->getId());
    }
}
