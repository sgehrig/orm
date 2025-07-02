<?php

declare(strict_types=1);

namespace Doctrine\Tests\Models\NativeLazy;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use TQ\Domain\Amount;

#[Entity]
class ImportedTransaction extends Transaction
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    private int $id = 0;

    #[ManyToOne(targetEntity: TransactionImport::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private TransactionImport $import;

    /** @var Collection<int, AccountTransaction> */
    #[OneToMany(targetEntity: AccountTransaction::class, mappedBy: 'sourceTransaction')]
    private Collection $allocatedTransactions;

    #[Column(type: Types::INTEGER)]
    private int $allocatedAmount = 0;

    public function __construct(TransactionImport $import, DateTimeImmutable $date, string $subject, int $amount)
    {
        parent::__construct($date, $subject, $amount);
        $this->import                = $import;
        $this->allocatedTransactions = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getImport(): TransactionImport
    {
        return $this->import;
    }

    /** @return array<int, AccountTransaction> */
    public function getAllocatedTransactions(): array
    {
        return $this->allocatedTransactions->toArray();
    }

    public function addAllocatedTransaction(AccountTransaction $transaction): self
    {
        if ($this->allocatedTransactions->contains($transaction)) {
            return $this;
        }

        $this->allocatedTransactions->add($transaction);
        $transaction->setSourceTransaction($this);

        return $this->updateAllocationStatus();
    }

    public function removeAllocatedTransaction(AccountTransaction $transaction): self
    {
        if (!$this->allocatedTransactions->contains($transaction)) {
            return $this;
        }

        $this->allocatedTransactions->removeElement($transaction);
        $transaction->setSourceTransaction(null);

        return $this->updateAllocationStatus();
    }

    public function getAllocatedAmount(): int
    {
        return $this->allocatedAmount;
    }

    private function updateAllocationStatus(): self
    {
        if (count($this->allocatedTransactions) === 0) {
            $this->allocatedAmount = 0;
            return $this;
        }

        $this->allocatedAmount = 0;
        foreach ($this->allocatedTransactions as $transaction) {
            $this->allocatedAmount += $transaction->getAmount();
        }
        return $this;
    }

}
