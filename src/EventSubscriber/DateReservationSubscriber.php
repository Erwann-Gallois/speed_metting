<?php

namespace App\EventSubscriber;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DateReservationSubscriber extends AbstractController  implements EventSubscriberInterface
{
    private $twig;

    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }
    public function onKernelRequest(RequestEvent $event): void
    {
        $filesystem = new Filesystem();
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
        $filename3 = $configDir . '/date_reservation.txt';
        $date = null;
        if ($filesystem->exists($filename3)) {
            $date = file_get_contents($filename3);
        }
        try {
            $date = \DateTime::createFromFormat('d/m/Y H:i', $date);
            if ($date === false) {
                throw new \Exception("La conversion de la date a échoué.");
            }
            // Utilisez $date comme un objet DateTime
        } catch (\Exception $e) {
            // Gérez l'erreur, par exemple en loggant l'erreur ou en informant l'utilisateur
            echo "Erreur lors du parsing de la date : " . $e->getMessage();
        }
        $now = new DateTime();
        $afficherLien = $now >= $date;

        $this->twig->addGlobal('afficherLien', $afficherLien);

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
