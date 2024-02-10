<?php
namespace App\Service;

class BookFormProccesor
{
    public function __construct(
        BookManager $bookManager,
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        Request $request,
        FileUploader $fileUploader
    )
    {
        
    }
    public function __invoke()
}