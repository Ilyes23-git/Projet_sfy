<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ModeleRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('Produits', name: 'produit')]
final class ProduitController extends AbstractController
{
    #[Route('/list', name: 'affichage_produits')]
    public function affichage(ProduitRepository $rep,CategorieRepository $rep1): Response
    {
        $cat = $rep1->findAll();
        $pr = $rep->findAll();
        return $this->render('', [
            'produits' => $pr,
            'categorie' => $cat,
        ]);
    }
}
