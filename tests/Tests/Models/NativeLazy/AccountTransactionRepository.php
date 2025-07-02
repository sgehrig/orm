<?php

namespace Doctrine\Tests\Models\NativeLazy;

use Doctrine\ORM\EntityRepository;

class AccountTransactionRepository extends EntityRepository
{
    public function findById(string $id): AccountTransaction|null
    {
        return $this->find($id);
    }
}
