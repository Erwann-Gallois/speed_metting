<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Form\ReservationCollectionType;
use App\Form\ReservationType;
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
    public function index(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        elseif($user->getSession() == 1) {
            return $this->redirectToRoute('reservation_ses1');
        }
        elseif($user->getSession() == 2) {
            return $this->redirectToRoute('reservation_ses2');
        }
        elseif($user->getType != 2) {
            return $this->redirectToRoute('compte');
        }
    }

    #[Route('/reservation/session_1', name: 'reservation_ses1')]
    #[Route('/reservation/session_2', name: 'reservation_ses2')]
    public function reservationSes1(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        elseif($user->getSession() != 1) {
            return $this->redirectToRoute('reservation');
        }
        $route = $request->attributes->get('_route');
        if ($route == 'reservation_ses1') {
            $session = 1;
        }
        else {
            $session = 2;
        }
        $heures = $this->slotRDV($session);
        $form = $this->createForm(ReservationType::class, null, [
            'professionals' => $this->doctrine->getRepository(User::class)->findBy(['type' => 1]),
            // 'heures' => $heures,
        ]);
        $form->handleRequest($request);

        return $this->render('reservation/reservation.html.twig', [
            'controller_name' => 'ReservationController',
            'user' => $user,
            'form' => $form->createView(),
            'heures' => $heures,
        ]);
    }
}
