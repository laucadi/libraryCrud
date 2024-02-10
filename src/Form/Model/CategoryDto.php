<?php

namespace App\Form\Model;

use App\Entity\Category;

class CategoryDto
{

    public $id;
    public $name;
    // Método de fábrica estático para crear una instancia de BookDto desde un objeto Book
    public static function createFromCategory(Category $category)
    {
        // Crear una nueva instancia de la clase BookDto
        $dto = new self();
        $dto->id = $category->getId();
        // Asignar el título de BookDto con el título de Book
        $dto->name = $category->getName();

        // Devolver la instancia de BookDto creada
        return $dto;
    }
}
