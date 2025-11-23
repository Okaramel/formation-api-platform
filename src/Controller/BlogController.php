<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Article;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\ArticleType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class BlogController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(ArticleRepository $articleRepository): Response
    {
        $latestArticles = $articleRepository->findBy([], ['created_at' => 'DESC'], 6);

        return $this->render('blog/home.html.twig', [
            'articles' => $latestArticles,
        ]);
    }

    #[Route('/articles', name: 'app_articles')]
    public function articles(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['created_at' => 'DESC']);

        return $this->render('blog/articles.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show', requirements: ['id' => '\d+'])]
    public function show(string $id, ArticleRepository $articleRepository): Response
    {
        $articleId = (int) $id;
        $article = $articleRepository->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $relatedArticles = $articleRepository->createQueryBuilder('a')
            ->where('a.user_id = :userId')
            ->andWhere('a.id != :currentId')
            ->setParameter('userId', $article->getUserId())
            ->setParameter('currentId', $id)
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('blog/article_show.html.twig', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }

    #[Route('/categories', name: 'app_categories')]
    public function categories(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('blog/categories.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show', requirements: ['id' => '\d+'])]
    public function categoryShow(string $id, CategoryRepository $categoryRepository): Response
    {
        $categoryId = (int) $id;
        $category = $categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        return $this->render('blog/category_show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/article/create', name: 'app_article_create')]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $article = new Article();
        
        $article->setUserId($this->getUser());
        
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUserId($this->getUser());
            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/article_create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $search = $request->query->get('search', '');
        $categorySearch = $request->query->get('category_search', '');
        $articles = [];
        $categories = [];

        if ($search) {
            $articles = $articleRepository->createQueryBuilder('a')
                ->join('a.user_id', 'u')
                ->where('a.Title LIKE :search OR a.content LIKE :search OR u.firstname LIKE :search OR u.lastname LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('a.created_at', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            $articles = $articleRepository->findBy([], ['created_at' => 'DESC']);
        }

        if ($categorySearch) {
            $categories = $categoryRepository->createQueryBuilder('c')
                ->where('c.title LIKE :search')
                ->setParameter('search', '%' . $categorySearch . '%')
                ->orderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $categories = $categoryRepository->findBy([], ['id' => 'ASC']);
        }

        return $this->render('blog/admin.html.twig', [
            'articles' => $articles,
            'search' => $search,
            'categories' => $categories,
            'category_search' => $categorySearch,
        ]);
    }

    #[Route('/article/{id}/edit', name: 'app_article_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(string $id, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $em): Response
    {
        $articleId = (int) $id;
        $article = $articleRepository->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Article modifié avec succès !');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('blog/article_edit.html.twig', [
            'form' => $form,
            'article' => $article,
        ]);
    }

    #[Route('/article/{id}/delete', name: 'app_article_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(string $id, ArticleRepository $articleRepository, EntityManagerInterface $em): Response
    {
        $articleId = (int) $id;
        $article = $articleRepository->find($articleId);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'Article supprimé avec succès !');
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/category/create', name: 'app_category_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function createCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        
        $form = $this->createForm(\App\Form\CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            $this->addFlash('success', 'Catégorie créée avec succès !');
            return $this->redirectToRoute('app_categories');
        }

        return $this->render('blog/category_create.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/category/{id}/edit', name: 'app_category_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function categoryEdit(string $id, Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
        $categoryId = (int) $id;
        $category = $categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée avec succès !');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('blog/category_edit.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/category/{id}/delete', name: 'app_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function categoryDelete(string $id, CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
        $categoryId = (int) $id;
        $category = $categoryRepository->find($categoryId);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $em->remove($category);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée avec succès !');
        return $this->redirectToRoute('app_admin');
    }
}