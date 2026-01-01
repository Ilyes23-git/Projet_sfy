<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'cart_')]
final class CartController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, CategorieRepository $categorieRepository): Response
    {
        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            $categories = $categorieRepository->findAll();
            return $this->render('cart/index.html.twig', [
                'cartItems' => [],
                'total' => 0,
                'categorie' => $categories,
            ]);
        }

        $ids = array_keys($cart);
        
        $query = $this->em->createQuery(dql: "SELECT p FROM App\Entity\Produit p WHERE p.id IN (:ids)");
        $query->setParameter(key: 'ids', value: $ids);
        
        $products = $query->getResult();

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product->getId()] = $product;
        }

        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $id => $quantity) {
            if (isset($productMap[$id])) {
                $produit = $productMap[$id];
                $itemTotal = $produit->getPrix() * $quantity;
                $total += $itemTotal;
                $cartItems[] = [
                    'produit' => $produit,
                    'quantity' => $quantity,
                    'total' => $itemTotal
                ];
            }
        }

        $categories = $categorieRepository->findAll();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
            'categorie' => $categories,
        ]);
    }

    #[Route('/add/{id}', name: 'add')]
    public function add(int $id, Request $request, SessionInterface $session): Response
    {
        $query = $this->em->createQuery(dql: "SELECT COUNT(p.id) FROM App\Entity\Produit p WHERE p.id = :id");
        $query->setParameter(key: 'id', value: $id);
        
        $count = $query->getSingleScalarResult();
        
        if ($count == 0) {
            $this->addFlash('error', 'Product not found!');
            return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('produit_affichage'));
        }

        $cart = $session->get('cart', []);
        
        if (isset($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        
        $session->set('cart', $cart);
        $this->addFlash('success', 'Product added to cart!');

        return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('produit_affichage'));
    }

    #[Route('/update', name: 'update')]
    public function update(Request $request, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        
        foreach ($request->request->all() as $key => $value) {
            if (strpos($key, 'quantity_') === 0) {
                $id = str_replace('quantity_', '', $key);
                $quantity = (int) $value;
                
                if ($quantity <= 0) {
                    unset($cart[$id]);
                } else {
                    $cart[$id] = $quantity;
                }
            }
        }
        
        $session->set('cart', $cart);
        $this->addFlash('success', 'Cart updated!');
        
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Product removed from cart!');
        }
        
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/clear', name: 'clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('cart');
        $this->addFlash('success', 'Cart cleared!');
        
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/count', name: 'count')]
    public function getCartCount(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $count = 0;
        
        foreach ($cart as $quantity) {
            $count += $quantity;
        }
        
        return new Response($count);
    }

    #[Route('/total', name: 'total')]
    public function getCartTotal(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            return new Response('0.00');
        }

        $ids = array_keys($cart);
        
        $query = $this->em->createQuery(dql: "SELECT p.id, p.prix FROM App\Entity\Produit p WHERE p.id IN (:ids)");
        $query->setParameter(key: 'ids', value: $ids);
        
        $products = $query->getResult();

        $total = 0;
        foreach ($products as $product) {
            $id = $product['id'];
            $price = $product['prix'];
            $quantity = $cart[$id];
            $total += $price * $quantity;
        }

        return new Response(number_format($total, 2, '.', ''));
    }

    #[Route('/check/{id}', name: 'check')]
    public function checkProductInCart(int $id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $inCart = isset($cart[$id]);
        $quantity = $inCart ? $cart[$id] : 0;
        
        return $this->json([
            'in_cart' => $inCart,
            'quantity' => $quantity
        ]);
    }

    #[Route('/product/exists/{id}', name: 'product_exists')]
    public function productExists(int $id): Response
    {
        $query = $this->em->createQuery(dql: "SELECT COUNT(p.id) FROM App\Entity\Produit p WHERE p.id = :id");
        $query->setParameter(key: 'id', value: $id);
        
        $exists = $query->getSingleScalarResult() > 0;
        
        return $this->json(['exists' => $exists]);
    }
}