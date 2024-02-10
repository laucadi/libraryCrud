<?php

namespace App\Controller\Api;

use App\Repository\BookRepository;

use App\Service\BookFormProccessor;
use App\Service\BookManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BooksController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(path="/books")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function getActions(
        BookManager $bookManager
    ) {
        return $bookManager->getRepository()->findAll();
    }
    /**
     * @Rest\Post(path="/books")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function postActions(
        BookManager $bookManager,
        BookFormProccessor $bookFormProccesor,
        Request $request,
    ) {
        $book = $bookManager->create();
        [$book, $error] = ($bookFormProccesor)($book, $request);
        $statusCode = $book ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data =  $book ?? $error;
        return View::create($data,$statusCode);
    }
    /**
     * @Rest\Post(path="/books/{id}")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function editActions(
        int $id,
        BookFormProccessor $bookFormProccesor,
        BookManager $bookManager,
        Request $request
    ) {
        $book = $bookManager->find($id);
        if (!$book) {
            return View::create('Book not found', Response::HTTP_BAD_REQUEST);
        }
        [$book, $error] = ($bookFormProccesor)($book, $request);
        $statusCode = $book ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST;
        $data =  $book ?? $error;
        return View::create($data,$statusCode);
    }

     /**
     * @Rest\Delete(path="/books/{id}")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function deleteActions(
        int $id, 
        BookManager $bookManager,
        Request $request
    ) {
        $book = $bookManager->find($id);
        if (!$book) {
            return View::create('Book not found', Response::HTTP_BAD_REQUEST);
        }
        $bookManager -> delete($book);
        return View::create('Book deleted', Response::HTTP_NO_CONTENT);
    }
}

/**
 * Se itera sobre las categorías asociadas al 
 * libro utilizando el método getCategories(). 
 * Por cada categoría, se crea un objeto CategoryDto utilizando el método estático 
 * createFromCategory y se agrega a la propiedad $categories del objeto BookDto.
 *  También se agrega a la colección $originalCategories.
 *En resumen, este código se utiliza para preparar y manipular
 * objetos DTO (BookDto y CategoryDto) en el contexto de la edición de un libro.
 *  Los DTOs son objetos que se utilizan para transferir datos entre capas de la 
 * aplicación y a menudo se utilizan en formularios y en la manipulación de datos 
 * antes de persistirlos en la base de datos.
 */
