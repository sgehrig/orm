<?php

namespace Doctrine\Tests\Models\NativeLazy;

use Doctrine\ORM\EntityRepository;

class ContractRepository extends EntityRepository
{
    public function findById(int $id): Contract|null
    {
        $unitOfWork = $this->getEntityManager()->getUnitOfWork();
        $contract   = $unitOfWork->tryGetById($id, Contract::class);
        if ($contract) {
            return $contract;
        }

        $qb = $this->createQueryBuilder('rc');
        $qb->addSelect(['ra', 'da', 'c'])
           ->innerJoin('rc.rentAccount', 'ra')
           ->innerJoin('rc.depositAccount', 'da')
           ->innerJoin('rc.company', 'c')
           ->andWhere('rc.id = :id')
           ->setParameter('id', $id);
        $contract = $qb->getQuery()
                       ->getOneOrNullResult();

        if (!$contract) {
            return null;
        }

        $this->applyAdditionalHydration([$contract->getId()]);

        return $contract;
    }

    /** @param list<int> $contractIds */
    private function applyAdditionalHydration(array $contractIds): void
    {
        if (empty($contractIds)) {
            return;
        }

        $this->createQueryBuilder('rc')
             ->select(['PARTIAL rc.{id}'])
             ->where('rc.id IN (:contracts)')
             ->setParameter('contracts', $contractIds)
             ->getQuery()
             ->getResult();
    }
}
