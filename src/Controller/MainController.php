<?php

namespace App\Controller;

use App\Form\EditPhotoType;

use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    /**
     * Controlleur de la page d'accueil
     */
    #[Route('/', name: 'main_home')]
    public function home(): Response
    {
        return $this->render('main/home.html.twig');
    }

    /**
     * Controleur page de profil
     */
    #[Route('/mon-profil/', name: 'main_profil')]
    #[IsGranted('ROLE_USER')]
    public function profil(): Response
    {
        return $this->render('main/profil.html.twig');
    }

    /**
     *Contrôleur de la page de modification de la photo de profil
     */
    #[Route('/changer-photo-de-profil/', name: 'main_edit-photo')]
    #[IsGranted('ROLE_USER')]
    public function editPhoto(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(EditPhotoType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $photo = $form->get('photo')->getData();

            $newFileName = 'user' . $this->getUser()->getId() . '.' . $photo->guessExtension();

            //Mise à jour du nom de la photo du compte connecté
            $this->getUser()->setPhoto($newFileName);
            $em = $doctrine->getManager();
            $em->flush();

            $photo->move(
                $this->getParameter('app.user.photo.directory'),
                $newFileName
            );

            //Message de succès
            $this->addFlash('success', 'Photo de profil modifiée avec succès!');

            //Redirection vers la page de profil
            return $this->redirectToRoute('main_profil');

        }

        return $this->render('main/edit_photo.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
