<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(Request $request): Response
    {
        //Formulaire de contact
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) { 
            $data = $form->getData();
            dump($data);
        }
        return $this->render('front/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
