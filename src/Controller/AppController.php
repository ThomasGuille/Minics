<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $repoProducts): Response
    {
        /*
            1-Sélectionner tous les produits
            2-Afficher les produits
            3-Créer une nouvelle méthode appProductDetails avec la route 'app/product/details/{id}' / app_product_details
            4-Afficher les infos du produit
        */

        $dbProducts = $repoProducts->findAll();
        // dump($dbProducts);

        return $this->render('app/index.html.twig', [
            'dbProducts' => $dbProducts
        ]);
    }

    #[Route('/product/details/{id}', name: 'app_product_details')]
    public function appProductDetails($id, ProductRepository $repoProduct): Response {
        $product = $repoProduct->find($id);
        // dump($product);
        return $this->render('app/product.details.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function appAbout(): Response
    {
        return $this->render('app/about.html.twig');
    }

    #[Route('/products', name: 'app_products')]
    public function appProducts(ProductRepository $product): Response
    {
        $dbProducts = $product->findAll();
        return $this->render('app/products.html.twig', [
            'dbProducts' => $dbProducts
        ]);
    }

    #[Route('/why', name: 'app_why')]
    public function appWhy(): Response
    {
        return $this->render('app/why.html.twig');
    }

    #[Route('/testimonials', name: 'app_testimonials')]
    public function appTestimonials(): Response
    {
        return $this->render('app/testimonials.html.twig');
    }

    #[Route('/account', name: 'app_account')]
    public function appAccount(): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_home');
        }
        return $this->render('app/account.html.twig');
    }
}
