<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BlogController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(ArticleRepository $articleRepository): Response
    {
        // Get latest 6 articles for homepage
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

    #[Route('/article/{id}', name: 'app_article_show')]
    public function show(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé');
        }

        // Get related articles (latest articles from same author)
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

    #[Route('/category/{id}', name: 'app_category_show')]
    public function categoryShow(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        return $this->render('blog/category_show.html.twig', [
            'category' => $category,
        ]);
    }
}