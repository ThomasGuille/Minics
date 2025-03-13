<?php

namespace App\Controller;

use App\Entity\OrderDetails;
use App\Entity\Orders;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        if(isset($cart[$id])){
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
        $this->addFlash('success', "L'article <strong class='text-white'>$productTitle</strong> a été retiré du panier.");

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

    #[Route('/cart/delete/', name: 'app_cart_delete')]
    public function cartDelete(SessionInterface $session)
    {
        $session->remove('cart');
        $this->addFlash("success", "Le panier a été vidé.");

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/payment', name: 'app_cart_payment')]
    public function cartPayment(SessionInterface $session, ProductRepository $productRepo, EntityManagerInterface $entityManager)
    {
        $cart = $session->get('cart');
        $total = 0;
        // dump($cart);
        foreach($cart as $id => $quantity){
            $product = $productRepo->find($id);
            $stockDb = $product->getStock();
            // dump($product);
            // dump($stockDb);
            if($stockDb < $quantity){
                if($stockDb > 0){
                    // dump('article ' . $product->getTitle() . ' stock insuffisant.');
                    // dump('stock restant: ' . $stockDb);
                    // dump('quantité commandée : ' . $quantity);

                    $this->addFlash("warning", "La quantité de l'article <strong>" . $product->getTitle() . "</strong> a été réduite car le stock est insuffisant.");
                    $cart[$id] = $stockDb;
                }else{
                    // dump('article ' . $product->getTitle() . ' rupture de stock.');
                    // dump('stock restant: ' . $stockDb);
                    // dump('quantité commandée : ' . $quantity);

                    $this->addFlash("danger", "L'article <strong>" . $product->getTitle() . "</strong> a été retiré car il n'y en a plus en stock.");
                    unset($cart[$id]);
                }
                $error = true;

                $session->set('cart', $cart);
            }
            $total += $product->getPrice() * $quantity;
        }

        if(!isset($error)){
            // Insertion dans la table SQL orders
            $order = new Orders;
            $order->setUser($this->getUser());
            $orderNumber = "MINICS" . date('dmY') . '-' . uniqid();
            $order->setOrderNumber($orderNumber);
            $order->setRising($total);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setState("En cours de traitement");

            $entityManager->persist($order);
            $entityManager->flush();

            // Insertion dans la table order_details
            foreach($cart as $id => $quantity){
                $orderDetails = new OrderDetails;
                $product = $productRepo->find($id);
                $orderDetails->setOrders($order);
                $orderDetails->setProduct($product);
                $orderDetails->setQuantity($quantity);
                $orderDetails->setPrice($product->getPrice());

                $product->setStock($product->getStock() - $quantity);
                $entityManager->persist($product);

                $entityManager->persist($orderDetails);
                $entityManager->flush();
            }

            $this->addFlash("success", "La commande a été effectuée. Numéro de commande: <strong>$orderNumber</strong>");
            $session->remove('cart');
        }

        return $this->redirectToRoute('app_cart');
    }
}
