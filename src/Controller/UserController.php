<?php

namespace App\Controller;

use App\Entity\User;
use Cassandra\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/utilisateur', name: 'utilisateur')]
final class ControllerUserController extends AbstractController
{
    #[Route('/add', name: 'add_user')]
    public function submit(Request $request,EntityManagerInterface $em): Response
    {
        $utilisateur = new User();

        $form = $this->createForm(UserType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Utilisateur enregistré avec succès !');

            return $this->redirectToRoute('add_user');
        }

        return $this->render('', [
            'form' => $form->createView(),
        ]);
    }
}
