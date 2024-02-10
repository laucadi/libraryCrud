<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Entity\Category;
use App\Form\Model\BookDto;
use App\Form\Model\CategoryDto;
use App\Form\Type\BookFormType;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Service\BookManager;
use App\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BooksController extends AbstractFOSRestController
{
    /**
     * @Rest\Get(path="/books")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function getActions(
        BookRepository $bookRepository
    ) {
        return $bookRepository->findAll();
    }
    /**
     * @Rest\Post(path="/books")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function postActions(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $fileUploader
    ) {
        $bookDto = new BookDto();
        $form = $this->createForm(BookFormType::class, $bookDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return new Response("", Response::HTTP_BAD_REQUEST);
        }
        if ($form->isValid()) {
            $filename = $fileUploader->uploadBase64File($bookDto->base64Image);
            $book = new Book();
            $book->setTitle($bookDto->title);
            $book->setImage($filename);
            $em->persist($book);
            $em->flush();
            return $book;
        }
        return $form;
    }
    /**
     * @Rest\Post(path="/books/{id}")
     * @Rest\View(serializerGroups={"book"},serializerEnableMaxDepthChecks=true)
     */
    public function editActions(
        int $id,
        BookManager $bookManager,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        Request $request,
        FileUploader $fileUploader
    ) {
        $book = $bookManager->find($id);
        if (!$book) {
            throw $this->createNotFoundException("Book not found");
        }
        /**
         * Se utiliza el método estático createFromBook 
         * de la clase BookDto para crear un objeto BookDto 
         * a partir del objeto $book. 
         * Este método es un ejemplo de 
         * un método de fábrica estático que 
         * inicializa un objeto BookDto basado en un objeto Book existente.
         */
        $bookDto = BookDto::createFromBook($book);
        /**
         * Se crea una nueva instancia de la clase ArrayCollection llamada
         *  $originalCategories. Esta colección se utiliza para rastrear 
         * las categorías originales del libro antes de realizar cambios.
         */
        $originalCategories = new ArrayCollection();
        foreach ($book->getCategories() as $category) {
            $categoryDto = CategoryDto::createFromCategory($category);
            $bookDto->categories[] = $categoryDto;
            $originalCategories->add($categoryDto);
        }

        $form = $this->createForm(BookFormType::class, $bookDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return new Response("", Response::HTTP_BAD_REQUEST);
        }
        if ($form->isValid()) {
            // remove categories
            foreach ($originalCategories as $originalCategoryDto) {
                if (!in_array($originalCategoryDto, $bookDto->categories)) {
                    $category = $categoryRepository->find($originalCategoryDto->id);
                    $book->removeCategory($category);
                }
            }
            //add categories
            foreach ($bookDto->categories as $newCategoryDto) {
                if (!$originalCategories->contains($newCategoryDto)) {
                    $category = $categoryRepository->find($originalCategoryDto->id ?? 0);
                    if (!$category) {
                        $category = new Category();
                        $category->setName($newCategoryDto->name);
                        $em->persist($category);
                    }
                    $book->addCategory($category);
                }
            }
            $book->setTitle($bookDto->title);
            if ($bookDto->base64Image) {
                $filename = $fileUploader->uploadBase64File($bookDto->base64Image);
                $book->setImage($filename);
            }
            $em->persist($book);
            $em->flush();
            $em->refresh($book);
            return $book;
        }
        return $form;
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
