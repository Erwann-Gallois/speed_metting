<?php

namespace App\EventSubscriber;

use App\Entity\Variable;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DateReservationSubscriber extends AbstractController  implements EventSubscriberInterface
{
    private $twig;
    private $security;
    private $doctrine;

    public function __construct(\Twig\Environment $twig, Security $security, ManagerRegistry $doctrine)
    {
        $this->twig = $twig;
        $this->security = $security;
        $this->doctrine = $doctrine;
    }
    public function onKernelRequest(RequestEvent $event): void
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        $date = $variable->getDateOuverResa();
        $now = new DateTime();
        $afficherLien = $now >= $date;
        $this->twig->addGlobal('afficherLien', $afficherLien);

        $user = $this->security->getUser();
        if ($user === null) {
            $this->twig->addGlobal('pro', null);
        }
        else {
            $pro = ($user->getType() === 1) ? true : false;
            $this->twig->addGlobal('pro', $pro);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
