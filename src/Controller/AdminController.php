<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/products', name: 'app_admin_products')]
    public function adminProducts(): Response
    {
        return $this->render('admin/products.html.twig');
    }

    #[Route('/admin/orders', name: 'app_admin_orders')]
    public function adminOrders(): Response
    {
        return $this->render('admin/orders.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function adminUsers(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/admin/category', name: 'app_admin_category')]
    public function adminCategory(Request $request, EntityManagerInterface $entityManager, CategoryRepository $repoCategory): Response
    {
        $category = new Category;
        $form = $this->createForm(CategoryFormType::class, $category);

        // $category->setTitle($_POST['title])
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $category->setCreatedAt(new \DateTimeImmutable());

            // stmt->prepare(INSERT INTO category VALUES (:title))
            // stmt->bindValue(":title", $category->getTitle(), PDO::PARAM_STR)
            $entityManager->persist($category);

            // stmt->execute()
            $entityManager->flush();

            // $_SESSION["success"] = "La catégorie a été enregistrée."
            $this->addFlash("success", "La catégorie a été enregistrée.");
            return $this->redirectToRoute("app_admin_category");
        }

        /*
        findAll():
        $data = $dbConnect->query(SELECT * FROM category);
        $dbCategory = $data->fetchAll(PDO::FETCH_ASSOC);

        La classe Repository contient des méthodes permettant uniquement d'exécuter des requêtes de sélection en BDD
            find($id), findAll(), findBy(), findOneBy()
        */

        $dbCategory = $repoCategory->findAll();
        // dump($dbCategory);

        return $this->render('admin/category.html.twig', [
            "categoryForm" => $form,
            "dbCategory" => $dbCategory
        ]);
    }

    #[Route('/admin/category/update/{id}', name: 'app_admin_category_update')]
    public function adminCategoryUpdate($id, Category $category, Request $request, EntityManagerInterface $entityManager, CategoryRepository $repoCategory): Response {
        // dump($category);
        // dump($id);

        // SELECT * FROM category WHERE id = $id
        // + fetch(PDO::FETCH_ASSOC);
        $category = $repoCategory->find($id);
        // dump($category);

        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            // UPDATE category SET title = $category->getTitle, description = $category->getDescription() WHERE id = $id;
            $entityManager->persist($category);
            $entityManager->flush();

            // dump($category->getTitle());
            $categoryTitle = $category->getTitle();

            $this->addFlash("success", "La catégorie <strong class='text-white'>$categoryTitle</strong> a été modifiée.");

            return $this->redirectToRoute('app_admin_category');
        }

        $dbCategory = $repoCategory->findAll();

        return $this->render("admin/category.html.twig", [
            'categoryForm' => $form,
            'dbCategory' => $dbCategory
        ]);
    }

    #[Route('/admin/category/delete/{id}', name: 'app_admin_category_delete')]
    public function adminCategoryDelete($id, EntityManagerInterface $entityManager, CategoryRepository $repoCategory) {
        $category = $repoCategory->find($id);
        dump($category);

        // DELETE FROM category WHERE id = $id
        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash("success", "La catégorie a été supprimée");

        return $this->redirectToRoute("app_admin_category");
    }
}
