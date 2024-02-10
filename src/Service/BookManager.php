<?php
namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Bool_;

class BookManager
 {
    private $em;
    private $bookRepository;
    public function __construct(EntityManagerInterface $em, BookRepository $bookRepository)
    {
        $this-> em = $em;
        $this-> bookRepository = $bookRepository;
    }
    public function find(int $id): ?Book
    {
        return $this->bookRepository->find($id);
    }           
    public function create():Book
    {
        $book = new Book();
        return $book;
    }
    public function save(Book $book): Book{
        $this->em->persist($book);
        $this->em->flush();
        return $book;
    }
    public function reload(Book $book): Book{
        $this->em->refresh($book);
        return $book;
    }

 }