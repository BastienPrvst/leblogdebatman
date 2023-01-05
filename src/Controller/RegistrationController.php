<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * Contrôleur de la page d'inscription
     */
    #[Route('/creer-un-compte/', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //Si l'utilisateur est déjà connecté, on le redirige de force sur la page d'accueil du site
        if ($this->getUser()) {
            return $this->redirectToRoute('main_home');
        }

        $user = new User();


        $form = $this->createForm(RegistrationFormType::class, $user);


        $form->handleRequest($request);

        //Si le formulaire a bien été envoyé et qu'il ne possède pas d'erreurs.
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            //Hydratation de la date d'inscription du nouvel utilisateur
            $user->setRegistrationDate(new \DateTime());

            //Sauvegarde du nouvel utilisateur en BDD
            $entityManager->persist($user);
            $entityManager->flush();

            //Message flash de succès
            $this->addFlash('success', 'Votre compte à bien été créé avec succès!');

            //Redirection de l'utilisateur vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}