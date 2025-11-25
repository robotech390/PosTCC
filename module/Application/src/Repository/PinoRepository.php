<?php

namespace Application\Repository;

use Application\Entity\Pino;
use Doctrine\ORM\EntityRepository;

class PinoRepository extends EntityRepository
{
    public function findAvailablePinos($esp32Id)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.leito', 'l')
            ->where('p.esp32 = :esp32Id')
            ->andWhere('l.id IS NULL')
            ->setParameter('esp32Id', $esp32Id)
            ->orderBy('p.numeroPino', 'ASC');


        return $qb->getQuery()->getResult();

    }
}