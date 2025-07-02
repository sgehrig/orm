<?php

namespace Doctrine\Tests\Models\NativeLazy;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\MappedSuperclass;

#[MappedSuperclass]
class Transaction
{
    #[Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $date;

    #[Column(type: Types::STRING)]
    private string $subject = '';

    #[Column(type: Types::INTEGER)]
    private int $amount = 0;

    public function __construct(DateTimeImmutable $date, string $subject, int $amount)
    {
        $this->date    = $date;
        $this->subject = $subject;
        $this->amount  = $amount;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
