<?php

namespace Doctrine\Tests\Models\NativeLazy;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

#[Entity(repositoryClass: ContractRepository::class)]
class Contract
{
    #[Id]
    #[Column(type: Types::INTEGER)]
    #[GeneratedValue]
    private int $id = 0;

    #[ManyToOne(targetEntity: Company::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Company $company;

    #[OneToOne(targetEntity: Account::class, cascade: ['persist'], orphanRemoval: true)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $rentAccount;

    #[OneToOne(targetEntity: Account::class, cascade: ['persist'], orphanRemoval: true)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Account $depositAccount;

    public function __construct(Company $company)
    {
        $this->company        = $company;
        $this->rentAccount    = new Account($this);
        $this->depositAccount = new Account($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getRentAccount(): Account
    {
        return $this->rentAccount;
    }

    public function getDepositAccount(): Account
    {
        return $this->depositAccount;
    }
}
