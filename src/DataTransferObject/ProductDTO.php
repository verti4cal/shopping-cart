<?php

namespace App\DataTransferObject;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProductDTO
{
    #[NotBlank]
    #[Length(max: 255)]
    public string $name;

    #[NotBlank]
    #[PositiveOrZero(message: 'Price must be positive')]
    public int $price;
}
