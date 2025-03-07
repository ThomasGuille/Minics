<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute("app_home");
        }

        $user = new User();
        // dump($user);

        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $form->get('password')->getData();
            $passwordHash = $userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($passwordHash);
            
            $entityManager->persist($user); // équivalent de prepare("INSERT INTO user VALUES (...)")
            $entityManager->flush();    // équivalent de execute()

            // dump($plainPassword);
            // dump($passwordHash);
            // dump($user);

            return $this->redirectToRoute('app_login');
        }



        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form
        ]);
    }
}
