<?php

namespace Doctrine\Tests\Models\NativeLazy;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Company
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }
}
