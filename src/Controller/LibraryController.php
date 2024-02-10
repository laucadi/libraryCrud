<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class LibraryController extends AbstractController
{
    //esta es una fomra de injectar servicios por medio del constructor
    // private $logger;
    // public function __construct(LoggerInterface $logger)
    // {
    //     $this->logger = $logger;
    // }

    /**
     * 
     * @Route("/books",name="books_get")
     *  */
    public function list(Request $request, BookRepository $bookRepository)
    {
        // los controladores pueden recibir servicios, clases de php
        //se alojan en containers= injeccion de dependencias
        $title = $request->get("title", "alegria");
        $books = $bookRepository->findAll();
        $booksAsArray = [];
        foreach ($books as $book) {
            $booksAsArray[] = [
                "id" => $book->getId(),
                "title" => $book->getTitle(),
                "image" => $book->getImage()
            ];
        };
        $response =  new JsonResponse();
        $response->setData([
            "success" => true,
            "data" => $booksAsArray,
        ]);
        return $response;
    }
    /**
     * 
     * @Route("book/create",name="create_name")
     *  */
    public function createBook(Request $request, EntityManagerInterface $em)
    {
        $book = new Book();
        $response =  new JsonResponse();
        $title = $request->get("title", null);
        if (empty($title)) {
            $response->setData([
                "success" => false,
                "error" => "title cannot be empty",
                "data" => null,
            ]);
            return $response;
        }
        $book->setTitle($title);
        //le dice a dice a doctrine que ese objeto de la clase libro que controlar
        $em->persist($book);
        //envia los objetos persistidos a base de datos
        $em->flush();
        $response =  new JsonResponse();
        $response->setData([
            "success" => true,
            "data" => [
                [
                    "id" => $book->getId(),
                    "title" => $book->getTitle()

                ],
            ]
        ]);
        return $response;
    }
}
