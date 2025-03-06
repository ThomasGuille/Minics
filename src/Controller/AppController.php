<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function appAbout(): Response
    {
        return $this->render('app/about.html.twig');
    }

    #[Route('/products', name: 'app_products')]
    public function appProducts(): Response
    {
        return $this->render('app/products.html.twig');
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

    #[Route('/register', name: 'app_register')]
    public function appRegister(): Response
    {
        return $this->render('registration/register.html.twig');
    }
}
