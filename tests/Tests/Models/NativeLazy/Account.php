<?php

namespace Doctrine\Tests\Models\NativeLazy;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class Account
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    private int $id = 0;

    #[ManyToOne(targetEntity: Contract::class)]
    #[JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private Contract|null $contract = null;

    #[Column(type: Types::INTEGER)]
    private int $balance = 0;

    public function __construct(Contract|null $contract)
    {
        $this->contract = $contract;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContract(): Contract|null
    {
        return $this->contract;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function deleteTransaction(AccountTransaction $transaction, DateTimeImmutable $deletedAt): AccountTransaction
    {
        return $transaction->deleteTransaction($deletedAt);
    }
}
