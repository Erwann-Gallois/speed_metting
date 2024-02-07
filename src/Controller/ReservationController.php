<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Form\ReservationCollectionType;
use App\Form\ReservationType;
use App\Repository\SessionRepository;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }

    private function getMaxPlaceRDV():int
    {
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
        $filename = $configDir . '/limite_places.txt';
        $limite = file_get_contents($filename);
        return $limite;
    }

    private function slotRDV(int $session):array
    {
        $slots = [];
        $sessions = [
            1 => ['14:00', '15:00'],
            2 => ['16:00', '17:00'],
        ];

        list($start, $end) = $sessions[$session];
        $start = new \DateTime($start);
        $end = new \DateTime($end);
        $i = 0;
        while ($start < $end) {
            $time = $start->format('H:i');
            $slots[$i] = $time;
            $start->modify('+10 minutes');
            $i++;
        }
        return $slots;
    }

    #[Route('/reservation', name: 'reservation')]
    public function reservation(Request $request, SessionRepository $srp): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        $session = $user->getSession();
        $heures = $this->slotRDV($session);
        $reservation = array();
        for ($i = 0; $i < count($heures); $i++) {
            $reservation['reservation'][$i] = new Session();
        }
        $form = $this->createForm(ReservationCollectionType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data['reservation'] as $key => $value) {
                $heure = new \DateTime($heures[$key]);
                $unique = $srp->findUniqueSession($value->getPro()->getId(), $heure, $user->getId());
                if (count($unique) > 0) {
                    $this->addFlash('danger', 'Vous avez déjà un rendez-vous à cette heure : '.$heure->format('H:i')." avec ce professionnel");
                    return $this->redirectToRoute('reservation');
                }
                $uniquerdv = $srp->findUniqueSessionProEleve($value->getPro()->getId(), $user->getId());
                if (count($uniquerdv) > 0) {
                    $this->addFlash('danger', 'Vous avez déjà un rendez-vous avec ce professionnel');
                    return $this->redirectToRoute('reservation');
                }
                $allrdv = $srp->findAllSessionPro($value->getPro()->getId(), $heure);
                if (count($allrdv) >= $this->getMaxPlaceRDV()) {
                    $this->addFlash('danger', 'Ce professionnel est déjà complet à cette heure');
                    return $this->redirectToRoute('reservation');
                }
                $value->setEleve($user);
                $value->setHeure($heure);
                $em = $this->doctrine->getManager();
                $em->persist($value);
                $em->flush();
            }
            $this->addFlash('success', 'Rendez-vous enregistré');
            return $this->redirectToRoute('accueil');
        }
        return $this->render('reservation/reservation.html.twig', [
            'controller_name' => 'ReservationController',
            'user' => $user,
            'forms' => $form->createView(),
            'heures' => $heures,
        ]);
    }
}
