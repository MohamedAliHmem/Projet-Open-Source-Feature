<?php

namespace App\Controller;

use App\Entity\BookSearch;
use App\Form\BookSearchType;
use App\Repository\BorrowingRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReportController extends AbstractController
{
    #[Route('/most-popular-book', name: 'most-popular-book')]
    public function index(BorrowingRepository $repository): Response
    {
        $books = $repository->findMostPopularBooksQb();
        return $this->render('report/index.html.twig', [
            'books' => $books,
        ]);
    }
    #[Route('/borrowingBook', name: 'app_book_search')]
    public function BorrowingBook(Request $request, BorrowingRepository $repository) {
        $bookSearch = new BookSearch();
        $form = $this->createForm(BookSearchType::class,$bookSearch);
        $form->handleRequest($request);
        $borrowings= [];
        if($form->isSubmitted() && $form->isValid()) {
        $book = $bookSearch->getBook();
        if ($book!="")
        $borrowings=$repository->findBy( array('book' => $book) );
        else
        $borrowings= $repository->findAll();
        }
        return $this->render('report/BorrowingBook.html.twig',
        ['form' => $form->createView(),'borrowings' => $borrowings]);
        }
        
}

