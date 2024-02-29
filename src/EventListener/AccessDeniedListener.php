<?php
// src/EventListener/AccessDeniedListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener
{
    private $session;
    private $urlGenerator;

    public function __construct(SessionInterface $session, UrlGeneratorInterface $urlGenerator)
    {
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Récupère l'exception
        $exception = $event->getThrowable();

        // Vérifie si l'exception est une AccessDeniedException
        if ($exception instanceof AccessDeniedException) {
            // Ajoute un message flash
            $this->session->getFlashBag()->add('danger', 'Accès refusé. Vous n\'avez pas les droits nécessaires pour accéder à cette page.');

            // Crée une réponse de redirection
            $response = new RedirectResponse($this->urlGenerator->generate('homepage'));

            // Définit la réponse pour l'événement
            $event->setResponse($response);
        }
    }
}
