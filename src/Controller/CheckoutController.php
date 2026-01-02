<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/checkout', name: 'checkout_')]
final class CheckoutController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, CategorieRepository $categorieRepository, ProduitRepository $produitRepository): Response
    {
        // Check if user is logged in
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Please login to checkout');
            return $this->redirectToRoute('app_login');
        }

        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('warning', 'Your cart is empty');
            return $this->redirectToRoute('cart_index');
        }

        $ids = array_keys($cart);
        
        // Get cart products
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

        return $this->render('checkout/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
            'categorie' => $categories,
        ]);
    }

    #[Route('/place-order', name: 'place')]
    public function placeOrder(Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
        // Check if user is logged in
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $session->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('error', 'Your cart is empty');
            return $this->redirectToRoute('cart_index');
        }

        // Get form data
        $paymentMethod = $request->request->get('payment', 'cash');
        $address = $request->request->get('address');
        $city = $request->request->get('city');
        $zipCode = $request->request->get('zip_code');

        // Validate required fields
        if (empty($address) || empty($city) || empty($zipCode)) {
            $this->addFlash('error', 'Please fill all required fields');
            return $this->redirectToRoute('checkout_index');
        }

        // Create full address
        $fullAddress = $address . ', ' . $city . ' ' . $zipCode;

        // Get cart products
        $ids = array_keys($cart);
        
        $query = $em->createQuery(dql: "SELECT p FROM App\Entity\Produit p WHERE p.id IN (:ids)");
        $query->setParameter(key: 'ids', value: $ids);
        $products = $query->getResult();

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product->getId()] = $product;
        }

        // Calculate total
        $total = 0;
        foreach ($cart as $id => $quantity) {
            if (isset($productMap[$id])) {
                $produit = $productMap[$id];
                $total += $produit->getPrix() * $quantity;
            }
        }

        // Fixed shipping fee of 7 TND
        $deliveryFee = 7.00;
        $finalTotal = $total + $deliveryFee;

        // Create and save the order
        $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setAdresse($fullAddress);
        $commande->setPrixTotal($finalTotal);
        $commande->setFraisLivraison($deliveryFee); // Always 7 TND
        $commande->setUser($this->getUser());

        // Add products to order
        foreach ($cart as $id => $quantity) {
            if (isset($productMap[$id])) {
                $produit = $productMap[$id];
                // Add product multiple times based on quantity
                for ($i = 0; $i < $quantity; $i++) {
                    $commande->addProduit($produit);
                }
            }
        }

        // Save to database
        $em->persist($commande);
        $em->flush();

        // Clear cart
        $session->remove('cart');
        
        $this->addFlash('success', 'Order placed successfully! Order ID: ' . $commande->getId());
        
        return $this->redirectToRoute('checkout_success', ['id' => $commande->getId()]);
    }

    #[Route('/success/{id}', name: 'success')]
    public function success(int $id, CategorieRepository $categorieRepository, EntityManagerInterface $em): Response
    {
        // Get the order with products
        $query = $em->createQuery(dql: 
            "SELECT c, p FROM App\Entity\Commande c 
             LEFT JOIN c.produits p 
             WHERE c.id = :id"
        );
        $query->setParameter(key: 'id', value: $id);
        $commande = $query->getOneOrNullResult();

        if (!$commande || $commande->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Order not found');
            return $this->redirectToRoute('produit_affichage');
        }

        $categories = $categorieRepository->findAll();

        return $this->render('checkout/success.html.twig', [
            'commande' => $commande,
            'categorie' => $categories,
        ]);
    }

    #[Route('/my-orders', name: 'my_orders')]
    public function myOrders(CategorieRepository $categorieRepository, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $userId = $this->getUser()->getId();
        
        $query = $em->createQuery(dql: 
            "SELECT c FROM App\Entity\Commande c 
             WHERE c.user = :userId 
             ORDER BY c.dateCommande DESC"
        );
        $query->setParameter(key: 'userId', value: $userId);
        $commandes = $query->getResult();

        $categories = $categorieRepository->findAll();

        return $this->render('checkout/my_orders.html.twig', [
            'commandes' => $commandes,
            'categorie' => $categories,
        ]);
    }
}