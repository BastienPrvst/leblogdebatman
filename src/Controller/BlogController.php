<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\CreateArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

            //Redirection de l'utilisateur vers l'article qu'il vient de créer
            return $this->redirectToRoute('blog_publication_view', [
                'slug' => $newArticle->getSlug()
,            ]);

        }


        // Pour que la vue puisse afficher le formulaire, on doit lui envoyer le formulaire généré, avec $form->createView()
        return $this->render('blog/new_publication.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * Contrôleur de la page qui liste tous les articles
     */
    #[Route('/publications/liste/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): response
    {
        //Récupération du numéro de la page demandée dans l'URL
        $requestedPage = $request->query->getInt('page', 1);

        //Vérification que le numéro est positif
        if($requestedPage < 1 ){
            throw new NotFoundHttpException();
        }
        //Manager général des entités
        $em = $doctrine->getManager();

        // Requête pour récupérer les articles
        $query =$em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        //Récupération des articles
        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles, //On envoie les articles à la vue twig
        ]);
    }

    /**
     * Contrôleur de la page permettant de voir un article en détail
     */
    #[Route('/publication/{slug}/', name: 'publication_view')]
    public function publicationView(Article $article): Response
    {
        dump($article);

        return $this->render('blog/publication_view.html.twig', [
            'article' => $article,
        ]);
    }


}
