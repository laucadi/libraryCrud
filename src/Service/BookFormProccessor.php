<?php
namespace App\Service;

use App\Entity\Book;
use App\Form\Model\BookDto;
use App\Form\Model\CategoryDto;
use App\Form\Type\BookFormType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class BookFormProccessor

{
    private $bookManager;
    private $categoryManager;
    private $fileUploader;
    private $formFactory;
    public function __construct(
        BookManager $bookManager,
        CategoryManager $categoryManager,
        FileUploader $fileUploader,
        FormFactoryInterface $formFactory,
    )
    {
        $this-> bookManager = $bookManager;
        $this-> categoryManager = $categoryManager;
        $this-> fileUploader = $fileUploader;
        $this->formFactory = $formFactory;
    }
    public function __invoke(Book $book, Request $request): array
    {
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

        $form = $this->formFactory -> create(BookFormType::class, $bookDto);
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return [null, 'form is not submitted'];
        }
        if ($form->isValid()) {
            // remove categories
            foreach ($originalCategories as $originalCategoryDto) {
                if (!in_array($originalCategoryDto, $bookDto->categories)) {
                    $category = $this->categoryManager->find($originalCategoryDto->id);
                    $book->removeCategory($category);
                }
            }
            //add categories
            foreach ($bookDto->categories as $newCategoryDto) {
                if (!$originalCategories->contains($newCategoryDto)) {
                    $category = $this->categoryManager->find($originalCategoryDto->id ?? 0);
                    if (!$category) {
                        $category = $this->categoryManager-> create();
                        $category->setName($newCategoryDto->name);
                        $this->categoryManager->persist($category);
                    }
                    $book->addCategory($category);
                }
            }
            $book->setTitle($bookDto->title);
            if ($bookDto->base64Image) {
                $filename = $this-> fileUploader->uploadBase64File($bookDto->base64Image);
                $book->setImage($filename);
            }
            $this->bookManager->save($book);
            $this->bookManager->reload($book);
            return [$book, null];
        }
        return [null, $form];
    }
}