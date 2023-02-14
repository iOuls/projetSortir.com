<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\User;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortiesRLController extends AbstractController
{
    #[Route('/sorties/sedesister/{id}', name: 'sorties_desister')]
    public function desister(
        EntityManagerInterface $em,
        SortieRepository       $sR,
        Sortie                 $sortie
    ): Response
    {
        if ($this->getUser() != null) {
            $user = new User();
            $user->setEmail($this->getUser()->getUserIdentifier());
            $sortie->removeParticipant($user);
            try {
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('Désistement effectué', 'Votre désistement a été pris en compte.');

                // TODO : vérifier la route
                return $this->redirectToRoute('sorties_list');

            } catch (\Exception $e) {
                $this->addFlash('Désistement non effectué', 'Détail de l\'erreur : ' . $e->getMessage());
                // TODO : vérifier la route
                return $this->redirectToRoute('sorties_list');
            }
        } else {
            // TODO : vérifier la route
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/sorties/cloture/{id}', name: 'sorties_cloture')]
    public function cloture(): Response
    {
        

        // TODO : vérifier la route
        return $this->redirectToRoute('sorties_list');
    }
}
