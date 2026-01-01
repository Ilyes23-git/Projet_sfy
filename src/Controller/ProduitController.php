<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ModeleRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Remove the class-level route annotation and put it on the method instead
final class ProduitController extends AbstractController
{
    #[Route('/Produits/list', name: 'produit_affichage')]
    public function affichage(ProduitRepository $rep, CategorieRepository $rep1): Response
    {
        $cat = $rep1->findAll();
        $pr = $rep->findAll();
        return $this->render('home/home.html.twig', [
            'produits' => $pr,
            'categorie' => $cat,
        ]);
    }
}