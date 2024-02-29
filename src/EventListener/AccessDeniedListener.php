<?php
// src/EventListener/AccessDeniedListener.php
namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener extends AbstractController implements EventSubscriberInterface 
{

    private $session;
    private $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->session = $requestStack->getSession();
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Récupère l'exception
        $exception = $event->getThrowable();

        // Vérifie si l'exception est une AccessDeniedException
        if ($exception instanceof AccessDeniedException) {
            // Ajoute un message flash
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');

            // Crée une réponse de redirection
            $response = new RedirectResponse($this->urlGenerator->generate('connexion'));

            // Définit la réponse pour l'événement
            $event->setResponse($response);
        }
    }
}
