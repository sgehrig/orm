<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;

use DateTimeImmutable;
use Doctrine\Tests\Models\NativeLazy\Account;
use Doctrine\Tests\Models\NativeLazy\AccountTransaction;
use Doctrine\Tests\Models\NativeLazy\ImportedTransaction;
use Doctrine\Tests\Models\NativeLazy\TransactionImport;
use Doctrine\Tests\OrmFunctionalTestCase;

class NativeLazyTest extends OrmFunctionalTestCase
{
    private int $accountId             = 0;
    private int $accountTransactionId  = 0;
    private int $importedTransactionId = 0;

    protected function setUp(): void
    {
        $this->useModelSet('nativeLazy');

        parent::setUp();

        $this->generateFixture();
    }

    public function generateFixture(): void
    {
        $import              = new TransactionImport();
        $importedTransaction = new ImportedTransaction(
            $import,
            new DateTimeImmutable('2023-10-01'),
            'Test Transaction',
            1000,
        );
        $account             = new Account();
        $accountTransaction  = new AccountTransaction(
            $account,
            new DateTimeImmutable('2023-10-02'),
            'Test Account Transaction',
            500,
        );
        $importedTransaction->addAllocatedTransaction($accountTransaction);

        $this->_em->persist($account);
        $this->_em->persist($import);
        $this->_em->persist($importedTransaction);
        $this->_em->persist($accountTransaction);
        $this->_em->flush();
        $this->_em->clear();

        $this->accountId             = $account->getId();
        $this->accountTransactionId  = $accountTransaction->getId();
        $this->importedTransactionId = $importedTransaction->getId();
    }

    public function testDeleteAccountTransaction(): void
    {
        $accountTransaction = $this->_em->getRepository(AccountTransaction::class)
                                        ->findById($this->accountTransactionId);
        self::assertInstanceOf(AccountTransaction::class, $accountTransaction);

        $importedTransaction = $accountTransaction->getSourceTransaction();

        self::assertNotNull($importedTransaction);
        $importedTransaction->removeAllocatedTransaction($accountTransaction);

        $account = $accountTransaction->getAccount();
        $account->deleteTransaction($accountTransaction, new DateTimeImmutable());

        $this->_em->flush();
        $this->_em->clear();

        $account = $this->_em->find(Account::class, $this->accountId);
        self::assertInstanceOf(Account::class, $account);
        self::assertEquals(0, $account->getBalance());

        $accountTransaction = $this->_em->find(AccountTransaction::class, $this->accountTransactionId);
        self::assertInstanceOf(AccountTransaction::class, $accountTransaction);
        self::assertTrue($accountTransaction->isDeleted());
        self::assertNull($accountTransaction->getSourceTransaction());

        $importedTransaction = $this->_em->find(ImportedTransaction::class, $this->importedTransactionId);
        self::assertCount(0, $importedTransaction->getAllocatedTransactions());
        self::assertEquals(0, $importedTransaction->getAllocatedAmount());
    }
}
