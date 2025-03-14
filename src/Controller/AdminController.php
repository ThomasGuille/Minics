<?php

namespace App\Controller;

use PDO;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Orders;
use App\Entity\User;
use App\Form\ProductFormType;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManager;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrdersRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(ProductRepository $productsRepo, UserRepository $userRepo, OrdersRepository $ordersRepo): Response
    {
        $products = $productsRepo->findAll();
        $users = $userRepo->findAll();
        $orders = $ordersRepo->findAll();
        dump($products);
        dump($users);
        dump($orders);

        

        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/products', name: 'app_admin_products')]
    #[Route('/admin/products/update/{id}', name: 'app_admin_product_update')]
    public function adminProducts(?Product $product, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, ProductRepository $repoProduct): Response
    {
        // ?Product $product : le ? veut dire que par défaut $product est null
        // dump($product);
        
        // Si $product n'est pas (!), alors c'est qu'il n'y a pas d'id en url, donc on crée un nouvel objet Product, dans le cas contraire (donc si il y a un id dans l'url), $product contient les données du produit correspondant, qui sont alors affichées dans le formulaire (symfony va directement chercher les infos en BDD)
        if(!$product){
            $product = new Product;
        }

        $form = $this->createForm(ProductFormType::class, $product);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            
            $pictureFile = $form->get('picture')->getData();
            // dump($pictureFile);
            if($pictureFile){
                $originalFileName = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME); // retourne le nom du fichier d'origine sans l'extension
                // dump($originalFileName);
                $safeFileName = $slugger->slug($originalFileName);  // sécurise le nom du fichier (supprime espaces,...)
                // dump($safeFileName);

                $newFileName = $safeFileName . '-' . uniqid() . '.' .$pictureFile->guessExtension();    // renomme le fichier (p1-67d02ac9ae173.png)
                // dump($newFileName); 
                // dump($this->getParameter('image_directory'));
                $currentPath = $this->getParameter('image_directory');  // récupère le chemin donné dans le fichier services.yaml
                try{
                    $pictureFile->move($currentPath, $newFileName); // copie le fichier dans le dossier spécifié
                }catch (FileException $e) {
                    dump($e);
                }
                $product->setPicture($newFileName);
            }

            if($product->getId()){
                $msgValid = "Les modifications ont été effectuées.";
            }else{
                $msgValid = "L'article a été enregistré.";
            }
            $product->setCreatedAt(new \DateTimeImmutable);
            // dump($product);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', $msgValid);
            return $this->redirectToRoute('app_admin_products');
        }

        // il est également possible de: $repoProduct = $entityManager->getRepository(Product::class); au lieu de l'importer en argument de la méthode adminProduct()
        $dbProduct = $repoProduct->findAll();

        return $this->render('admin/products.html.twig', [
            'productForm' => $form,
            'dbProduct' => $dbProduct,
            'pictureFile' => $product->getPicture()
        ]);
    }

    #[Route('/admin/products/delete/{id}', name: 'app_admin_product_delete')]
    public function adminProductDelete($id, EntityManagerInterface $entityManager, ProductRepository $repoProduct)
    {
        $product = $repoProduct->find($id);
        dump($product);
        $productTitle = $product->getTitle();
        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', "Le produit <strong class='text-white'>$productTitle</strong> a été supprimé.");
        return $this->redirectToRoute('app_admin_products');
    }


    #[Route('/admin/orders', name: 'app_admin_orders')]
    public function adminOrders(): Response
    {
        return $this->render('admin/orders.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function adminUsers(UserRepository $userRepo): Response
    {
        $dbUser = $userRepo->findAll();
        // dump($dbUser);

        return $this->render('admin/users.html.twig', [
            'dbUser' => $dbUser
        ]);
    }

    #[Route('/admin/users/update/{id}', name: 'app_admin_users_update')]
    public function adminUserUpdate(UserRepository $userRepo, User $user, Request $request, EntityManagerInterface $entityManager)
    {
        $id = $user->getId();
        $currentUser = $userRepo->find($id);
        $newRole = $request->request->get('role');
        // dump($currentUser);
        // dump($newRole);
        $user->setRoles([$newRole]);
        // dump($user);
        $entityManager->persist($currentUser);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users');
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
            $categoryTitle = $category->getTitle();

            // $_SESSION["success"] = "La catégorie a été enregistrée."
            $this->addFlash("success", "La catégorie <strong class='text-white'>$categoryTitle</strong> a été enregistrée.");
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
        if($category->getProducts()->isEmpty()){
            $categoryTitle = $category->getTitle();
            $entityManager->remove($category);  // DELETE FROM category WHERE id = $id
            $entityManager->flush();
            $this->addFlash("success", "La catégorie <strong class='text-white'>$categoryTitle</strong> a été supprimée");
        }else{
            $this->addFlash("danger", "Impossible de supprimer une catégorie liée à un produit existant.");
        }


        return $this->redirectToRoute("app_admin_category");
    }
}
