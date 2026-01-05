<?php

namespace Application\Repository;

use Application\Entity\Paciente;
use Doctrine\ORM\EntityRepository;

class PacienteRepository extends EntityRepository
{
    public function findHistorico(Paciente $paciente, \DateTime $startDate, \DateTime $endDate = null)
    {
        if ($endDate === null) {
            $endDate = new \DateTime();
        }

        $qb = $this->createQueryBuilder('ps');

        $qb->where('ps.paciente = :paciente')
            ->andWhere('ps.dataRegistro BETWEEN :startDate AND :endDate')
            ->orderBy('ps.dataRegistro', 'ASC')
            ->setParameter('paciente', $paciente)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }

    public function findPatientsWithDetails(array $pacienteIds)
    {
        if (empty($pacienteIds)) {
            return [];
        }

        $qb = $this->createQueryBuilder('p');

        $qb->select([
            'p.id',
            'pes.nome as name',
            'pes.foto as photo',
            'l.numero as room'
        ])
            ->leftJoin('p.pessoa', 'pes')
            ->leftJoin('p.leito', 'l')
            ->where($qb->expr()->in('p.id', ':pacienteIds'))
            ->setParameter('pacienteIds', $pacienteIds);

        return $qb->getQuery()->getResult();
    }

}