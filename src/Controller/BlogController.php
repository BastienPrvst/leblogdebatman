<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\CreateArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Préfixe de la route et du nom de toutes les pages de la partie blog du site
 */
#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{

    /**
     * Contrôleur de la page permettant de créer un nouvel article
     */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function createArticle(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'un nouvel objet de la classe Article, vide pour le moment
        $newArticle = new Article();

        // Création d'un nouveau formulaire à partir de notre formulaire CreateArticleFormType et de notre nouvel article encore vide
        $form = $this->createForm(CreateArticleFormType::class, $newArticle);

        $form->handleRequest($request);

        //Si le formulaire a bien été envoyé et qu'il ne possède pas d'erreurs.
        if ($form->isSubmitted() && $form->isValid()){

            //Hydratation de la date de publication
            $newArticle
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
            ;

            //Sauvegarde du nouvel article en BDD
            $entityManager->persist($newArticle);
            $entityManager->flush();

            //Message flash de succès
            $this->addFlash('success', 'Votre article à bien été créé avec succès!');

        }


        // Pour que la vue puisse afficher le formulaire, on doit lui envoyer le formulaire généré, avec $form->createView()
        return $this->render('blog/new_publication.html.twig', [
            'form' => $form->createView()
        ]);
    }


}
