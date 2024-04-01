<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LangageController extends AbstractController
{
    #[Route('/change-langage/{_locale}', name: 'change_langage')]
    public function changeLanguage(Request $request, string $_locale): Response
    {
        // Vérifiez si la locale est supportée par votre application
        $supportedLocales = ['en', 'fr']; // Ajoutez vos locales supportées ici
        if (!in_array($_locale, $supportedLocales)) {
            throw $this->createNotFoundException('The language does not exist');
        }

        // Sauvegardez la locale choisie dans la session de l'utilisateur
        $request->getSession()->set('_locale', $_locale);

        // Redirigez l'utilisateur vers la page précédente ou par défaut
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer ?: $this->generateUrl('accueil'));
    }
}
