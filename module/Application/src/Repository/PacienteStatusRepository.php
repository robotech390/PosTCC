<?php

namespace Application\Repository;

use Application\Entity\Paciente;
use Doctrine\ORM\EntityRepository;

class PacienteStatusRepository extends EntityRepository
{
    public function findHistorico(Paciente $paciente, \DateTime $fiveDaysAgo)
    {
        $qb = $this->createQueryBuilder('ps')
        ->where('ps.paciente = :paciente')
        ->andWhere('ps.dataRegistro >= :fiveDaysAgo')
        ->orderBy('ps.dataRegistro', 'DESC');

        $qb->setParameter('fiveDaysAgo', $fiveDaysAgo);
        $qb->setParameter('paciente', $paciente);

        return $qb->getQuery()->getResult();

    }

    public function findPatientsWithAwakenings(\DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->createQueryBuilder('ps');

        $qb->select(['IDENTITY(ps.paciente) as id', 'COUNT(ps.id) as awakenings'])
        ->where('ps.dataRegistro BETWEEN :startDate AND :endDate')
        ->andWhere('ps.evento = :acordado')
        ->groupBy('ps.paciente')
        ->having('COUNT(ps.id) > 0')
        ->orderBy('awakenings', 'DESC')
        ->setParameter('startDate', $startDate)
        ->setParameter('endDate', $endDate)
        ->setParameter('acordado', true);

        return $qb->getQuery()->getResult();
    }

    public function getAverageDuration($pacienteId, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->createQueryBuilder('ps');

        $qb->select('ps')
            ->where('ps.paciente = :pacienteId')
            ->andWhere('ps.dataRegistro BETWEEN :startDate AND :endDate')
            ->orderBy('ps.dataRegistro', 'ASC')
            ->setParameter('pacienteId', $pacienteId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $events = $qb->getQuery()->getResult();

        if (empty($events)) {
            return null;
        }

        $durations = [];
        $lastAwakeTime = null;

        foreach ($events as $event) {
            $eventoStatus = $event->getEvento();
            $dataRegistro = $event->getDataRegistro();

            if ($eventoStatus === '1') {
                $lastAwakeTime = $dataRegistro;

            } elseif ($eventoStatus === '0' && $lastAwakeTime !== null) {
                $sleepTime = $dataRegistro;
                $diff = $sleepTime->getTimestamp() - $lastAwakeTime->getTimestamp();
                $minutes = $diff / 60;

                if ($minutes > 0 && $minutes < 1440) {
                    $durations[] = $minutes;
                }

                $lastAwakeTime = null;
            }
        }

        if (empty($durations)) {
            return null;
        }

        $average = array_sum($durations) / count($durations);

        return round($average);
    }

    public function getLastAwakeningDate($pacienteId, \DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->createQueryBuilder('ps');

        $qb->select('ps.dataRegistro')
            ->where('ps.paciente = :pacienteId')
            ->andWhere('ps.dataRegistro BETWEEN :startDate AND :endDate')
            ->andWhere('ps.evento = :acordado')
            ->orderBy('ps.dataRegistro', 'DESC')
            ->setMaxResults(1)
            ->setParameter('pacienteId', $pacienteId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('acordado', true);

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findPatientsWithTotalAwakeTime(\DateTime $startDate, \DateTime $endDate)
    {
        $qb = $this->createQueryBuilder('ps');

        $qb->select('ps')
            ->where('ps.dataRegistro BETWEEN :startDate AND :endDate')
            ->orderBy('ps.paciente', 'ASC')
            ->addOrderBy('ps.dataRegistro', 'ASC')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $events = $qb->getQuery()->getResult();

        // Processar eventos para calcular tempo total desperto
        $patientTimes = [];
        $currentPatient = null;
        $lastAwakeTime = null;

        foreach ($events as $event) {

            $pacienteId = $event->getPaciente()->getId();

            if (!isset($patientTimes[$pacienteId])) {
                $patientTimes[$pacienteId] = 0;
            }

            if ($event->getEvento() === '1') {
                $lastAwakeTime = $event->getDataRegistro();

            } elseif ($event->getEvento() === '0' && $lastAwakeTime !== null) {
                $diff = $event->getDataRegistro()->getTimestamp() - $lastAwakeTime->getTimestamp();
                $minutes = $diff / 60;

                if ($minutes > 0 && $minutes < 1440) {
                    $patientTimes[$pacienteId] += $minutes;
                }

                $lastAwakeTime = null;
            }

            $currentPatient = $pacienteId;
        }

        $result = [];
        foreach ($patientTimes as $pacienteId => $totalMinutes) {
            if ($totalMinutes > 0) {
                $result[] = [
                    'pacienteId' => $pacienteId,
                    'totalMinutes' => round($totalMinutes)
                ];
            }
        }

        usort($result, function($a, $b) {
            return $b['totalMinutes'] - $a['totalMinutes'];
        });

        return $result;
    }

    public function findInactivePatientsInPeriod(\DateTime $startDate, \DateTime $endDate)
    {
        $em = $this->getEntityManager();

        // Buscar todos os pacientes ativos
        $qbAllPatients = $em->createQueryBuilder();
        $qbAllPatients->select('p. id')
            ->from(Paciente::class, 'p');

        $allPatientIds = array_column($qbAllPatients->getQuery()->getResult(), 'id');

        if (empty($allPatientIds)) {
            return [];
        }

        // Buscar pacientes que TIVERAM atividade no período
        $qbActive = $this->createQueryBuilder('ps');
        $qbActive->select('DISTINCT IDENTITY(ps.paciente) as pacienteId')
            ->where('ps.dataRegistro BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $activePacienteIds = array_column($qbActive->getQuery()->getResult(), 'pacienteId');

        // Pacientes inativos = todos - ativos
        $inactivePacienteIds = array_diff($allPatientIds, $activePacienteIds);

        if (empty($inactivePacienteIds)) {
            return [];
        }

        // Buscar última atividade de cada paciente inativo
        $result = [];
        foreach ($inactivePacienteIds as $pacienteId) {
            $qbLastActivity = $this->createQueryBuilder('ps');
            $qbLastActivity->select('ps.dataRegistro')
                ->where('ps.paciente = :pacienteId')
                ->orderBy('ps.dataRegistro', 'DESC')
                ->setMaxResults(1)
                ->setParameter('pacienteId', $pacienteId);

            try {
                $lastActivity = $qbLastActivity->getQuery()->getSingleScalarResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
                $lastActivity = null;
            }

            $result[] = [
                'pacienteId' => $pacienteId,
                'lastActivity' => $lastActivity
            ];
        }

        return $result;
    }
}