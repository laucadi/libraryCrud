<?php

namespace App\Form\Model;

use App\Entity\Book;

class BookDto
{
    // Propiedades públicas de la clase
    public $title;
    public $base64Image;
    public $categories;

    // Constructor de la clase
    public function __construct()
    {
        $this->categories = [];
    }

    // Método de fábrica estático para crear una instancia de BookDto desde un objeto Book
    public static function createFromBook(Book $book)
    {
        // Crear una nueva instancia de la clase BookDto
        $dto = new self();

        // Asignar el título de BookDto con el título de Book
        $dto->title = $book->getTitle();

        // Devolver la instancia de BookDto creada
        return $dto;
    }
}
