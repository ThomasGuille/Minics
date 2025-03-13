<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(SessionInterface $session, ProductRepository $repoProduct): Response
    {
        // On r&cupère les données du panier dans la session
        $cart = $session->get('cart');
        // dump($cart);
        // On initialise
        $dataCart = [];
        $total = 0;

        // On boucle la session
        // $id est l'id du produit récupéré pour chaque tour de boucle
        // $quantity est la quantité
        if(!empty($cart)){
            foreach($cart as $id => $quantity){
                $product = $repoProduct->find($id); // On sélectionne en BDD les infos relative à l'objet correspondant à l'id
                // dump($product);
    
                // On ajoute les données bouclées dans le tableau ARRAY
                $dataCart[] = [
                    "product" => $product,  // On envoie l'objet Entity Product directement dans l'array
                    "quantity" => $quantity
                ];
    
                $total += $product->getPrice() * $quantity; // Calcul du montant total
            }
        }
        // dump($dataCart);
        // dump($total);

        return $this->render('cart/index.html.twig', [
            'dataCart' => $dataCart,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function cartAdd(Request $request, Product $product, SessionInterface $session)
    {
        // Création du panier dans la session
        $cart = $session->get('cart', []);
        $id = $product->getId();    // On stock l'id du produit sélectionné dans une variable
        $quantity = $request->request->get('quantity'); // On stock la quantité saisie dans le formulaire dans une variable

        // dump($id);
        // dump($quantity);
        
        if(isset($cart[$id])){
            $cart[$id] = $cart[$id] + $quantity;
        }else{
            $cart[$id] = $quantity;
        }
        
        // On sauvegarde la session
        $session->set('cart', $cart);
        // dump($cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/removeproduct/{id}', name: 'app_cart_remove_product')]
    public function cartRemoveProduct($id, SessionInterface $session, ProductRepository $repoProduct)
    {
        $cart = $session->get('cart');
        $product = $repoProduct->find($id);
        $productTitle = $product->getTitle();
        // dump($cart);
        unset($cart[$id]);
        $session->set('cart', $cart);
        $this->addFlash('success', "Le produit <strong class='text-white'>$productTitle</strong> a été retiré du panier.");

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/addquantity/{id}', name: 'app_cart_add_quantity')]
    public function cartAddQuantity($id, SessionInterface $session)
    {
        $cart = $session->get('cart');
        $cart[$id] = $cart[$id] + 1;
        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/removequantity/{id}', name: 'app_cart_remove_quantity')]
    public function cartRemoveQuantity($id, SessionInterface $session)
    {
        $cart = $session->get('cart');
        $cart[$id] = $cart[$id] - 1;
        $session->set('cart', $cart);

        return $this->redirectToRoute('app_cart');
    }
}
