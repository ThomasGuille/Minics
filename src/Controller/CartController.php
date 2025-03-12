<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->render('cart/index.html.twig', []);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function cartAdd(Request $request, Product $product, SessionInterface $session)
    {
        // Création du panier dans la session
        $cart = $session->get('cart', []);
        $id = $product->getId();    // On stock l'id du produit sélectionné dans une variable
        $quantity = $request->request->get('quantity'); // On stock la quantité saisie dans le formulaire dans une variable

        dump($id);
        dump($quantity);
        
        if(isset($cart[$id])){
            $cart[$id] = $cart[$id] + $quantity;
        }else{
            $cart[$id] = $quantity;
        }
        
        // On sauvegarde la session
        $session->set('cart', $cart);
        dump($cart);
    }
}
