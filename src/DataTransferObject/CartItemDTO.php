<?php

namespace App\DataTransferObject;

use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class CartItemDTO
{
    #[NotBlank]
    #[Uuid]
    public string $productUuid;

    #[NotBlank]
    #[PositiveOrZero]
    public int $quantity;
}
