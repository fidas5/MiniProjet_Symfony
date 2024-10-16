<?php 
namespace App\Controller;
use App\Controller\PropertySearchType;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;
use App\Entity\Category;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use App\Entity\PropertySearch;


class IndexController extends AbstractController 
{
    /**
     * @Route("/article/save", name="article_save")
     */
    public function save(EntityManagerInterface $entityManager) {
        // Create a new article
        $article = new Article();
        $article->setNom('Article 1');
        $article->setPrix(1000);

        // Save the article in the database
        $entityManager->persist($article);
        $entityManager->flush();

        return new Response('Article saved with ID ' . $article->getId());
    }

  /**
 * @Route("/", name="article_list")
 */
public function home(Request $request, EntityManagerInterface $entityManager): Response
{
    $propertySearch = new PropertySearch();
    $form = $this->createForm(PropertySearchType::class, $propertySearch);
    $form->handleRequest($request);

    // Initialement, le tableau des articles est vide
    $articles = [];

    // Vérifie si le formulaire a été soumis et est valide
    if ($form->isSubmitted() && $form->isValid()) {
        // On récupère le nom d'article tapé dans le formulaire
        $nom = $propertySearch->getNom();

        // On utilise l'EntityManager pour accéder au repository des articles
        $repository = $entityManager->getRepository(Article::class);

        // Si on a fourni un nom d'article, on affiche tous les articles ayant ce nom
        if ($nom !== "") {
            $articles = $repository->findBy(['nom' => $nom]);
        } else {
            // Si aucun nom n'est fourni, on affiche tous les articles
            $articles = $repository->findAll();
        }
    }

    return $this->render('articles/index.html.twig', [
        'form' => $form->createView(),
        'articles' => $articles,
    ]);
}



    /** 
     * @Route("/article/new", name="new_article", methods={"GET", "POST"}) 
     */
    public function new(Request $request, EntityManagerInterface $entityManager) { 
        $article = new Article(); 
        $form = $this->createForm(ArticleType::class, $article); 
        $form->handleRequest($request); 

        if ($form->isSubmitted() && $form->isValid()) { 
            $entityManager->persist($article); 
            $entityManager->flush(); 

            return $this->redirectToRoute('article_list'); 
        }

        return $this->render('articles/new.html.twig', ['form' => $form->createView()]); 
    }

   /**
 * @Route("/article/edit/{id}", name="article_edit", methods={"GET", "POST"})
 */
public function edit(Request $request, $id, EntityManagerInterface $entityManager)
{
    $article = $entityManager->getRepository(Article::class)->find($id);

    if (!$article) {
        throw $this->createNotFoundException('Article not found.');
    }

    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('article_list');
    }

    return $this->render('articles/edit.html.twig', ['form' => $form->createView()]);
}

    /**
     * @Route("/article/{id}", name="article_show")
     */
    public function show($id, EntityManagerInterface $entityManager) {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found.');
        }

        return $this->render('articles/show.html.twig', ['article' => $article]);
    }

    /**
     * @Route("/article/delete/{id}", name="delete_article", methods={"DELETE"})
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, $id) {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
            $entityManager->remove($article);
            $entityManager->flush();
        }

        return $this->redirectToRoute('article_list');
    }

    /**
     * @Route("/category/newCat", name="new_category")
     */
    public function newCategory(Request $request, EntityManagerInterface $entityManager) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('article_list');  // Redirect to the article list route
        }

        return $this->render('articles/newCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }
     /**
     * @Route("/art_cat/", name="article_par_cat", methods={"GET", "POST"})
     */
    public function articlesParCategorie(Request $request, EntityManagerInterface $entityManager)
    {
        $categorySearch = new CategorySearch();
        $form = $this->createForm(CategorySearchType::class, $categorySearch);
        $form->handleRequest($request);
    
        $articles = [];
    
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $categorySearch->getCategory();
            if ($category != "") {
                // Assurez-vous que la méthode getArticles() existe sur l'entité Category
                $articles = $category->getArticles();
            } else {
                $articles = $entityManager
                    ->getRepository(Article::class)
                    ->findAll();
            }
        }
    
        return $this->render('articles/articlesParCategorie.html.twig', [
            'form' => $form->createView(),
            'articles' => $articles,
        ]);
    }
    /**
 * @Route("/art_prix/", name="article_par_prix", methods={"GET", "POST"})
 */
public function articlesParPrix(Request $request, EntityManagerInterface $entityManager): Response
{
    $priceSearch = new PriceSearch();
    $form = $this->createForm(PriceSearchType::class, $priceSearch);
    $form->handleRequest($request);

    $articles = [];

    if ($form->isSubmitted() && $form->isValid()) {
        $minPrice = $priceSearch->getMinPrice();
        $maxPrice = $priceSearch->getMaxPrice();
        $articles = $entityManager->getRepository(Article::class)->findByPriceRange($minPrice, $maxPrice);
    }

    return $this->render('articles/articlesParPrix.html.twig', [
        'form' => $form->createView(),
        'articles' => $articles,
    ]);
}

    
}
