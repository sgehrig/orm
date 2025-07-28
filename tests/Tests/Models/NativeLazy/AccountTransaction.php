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
class AccountTransaction extends Transaction
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    private int $id = 0;

    #[ManyToOne(targetEntity: Account::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $account;

    #[ManyToOne(targetEntity: ImportedTransaction::class, inversedBy: 'allocatedTransactions')]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ImportedTransaction|null $sourceTransaction = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private DateTimeImmutable|null $deletedAt = null;

    public function __construct(Account $account, DateTimeImmutable $date, string $subject, int $amount)
    {
        parent::__construct($date, $subject, $amount);
        $this->account = $account;
        $this->account->setBalance($this->account->getBalance() + $amount);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getSourceTransaction(): ImportedTransaction|null
    {
        return $this->sourceTransaction;
    }

    public function setSourceTransaction(ImportedTransaction|null $transaction): self
    {
        if ($transaction === null) {
            $this->sourceTransaction = null;

            return $this;
        }

        if ($transaction === $this->sourceTransaction) {
            return $this;
        }

        $transaction->removeAllocatedTransaction($this);
        $this->sourceTransaction = $transaction;
        $transaction->addAllocatedTransaction($this);

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function delete(DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function deleteTransaction(DateTimeImmutable $deletedAt): self
    {
        $this->account->setBalance($this->account->getBalance() - $this->getAmount());
        $this->delete($deletedAt);
        return $this;
    }
}
