<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Entity\Cidade;
use Application\Entity\Endereco;
use Application\Entity\Estado;
use Application\Entity\Paciente;
use Application\Entity\PacienteStatus;
use Application\Entity\Pessoa;
use Application\Form\PessoaForm;
use Doctrine\ORM\EntityManager;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class RelatorioController extends AbstractActionController
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function frequenciaDespertoAction()
    {
        return new ViewModel([
            'itemsPerPage' => 3,
            'reportType' => 'frequencia'
        ]);
    }

    public function tempoDespertoAction()
    {
        return new ViewModel([
            'itemsPerPage' => 6,
            'reportType' => 'tempo'
        ]);
    }

    public function negacaoUsoAction()
    {
        return new ViewModel([
            'itemsPerPage' => 6,
            'reportType' => 'inatividade'
        ]);
    }

    public function fetchInactivePatientsAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');

        //try {
            $startDate = $this->params()->fromQuery('start_date');
            $endDate = $this->params()->fromQuery('end_date');

            if (empty($startDate) || empty($endDate)) {
                $response->setContent(json_encode([
                    'success' => false,
                    'error' => 'As datas de início e fim são obrigatórias'
                ]));
                return $response;
            }

            $startDateTime = new \DateTime($startDate .  ' 00:00:00');
            $endDateTime = new \DateTime($endDate . ' 23:59:59');

            $pacienteStatusRepo = $this->entityManager->getRepository(PacienteStatus::class);
            $pacienteRepo = $this->entityManager->getRepository(Paciente::class);

            // Buscar pacientes inativos
            $inactivePatients = $pacienteStatusRepo->findInactivePatientsInPeriod(
                $startDateTime,
                $endDateTime
            );

            if (empty($inactivePatients)) {
                $response->setContent(json_encode([
                    'success' => true,
                    'patients' => [],
                    'total' => 0
                ]));
                return $response;
            }

            $pacienteIds = array_column($inactivePatients, 'pacienteId');
            $lastActivityMap = [];
            foreach ($inactivePatients as $data) {
                $lastActivityMap[$data['pacienteId']] = $data['lastActivity'];
            }

            $patientsDetails = $pacienteRepo->findPatientsWithDetails($pacienteIds);

            $result = [];
            foreach ($patientsDetails as $patient) {
                $pacienteId = $patient['id'];

                $result[] = [
                    'id' => $pacienteId,
                    'name' => $patient['name'],
                    'room' => $patient['room'] ?? 'Sem leito',
                    'lastActivity' => $this->formatDate($lastActivityMap[$pacienteId]),
                    'photo' => $this->formatPhotoUrl($patient['photo'])
                ];
            }

            // Ordenar por nome
            usort($result, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });

            $response->setContent(json_encode([
                'success' => true,
                'patients' => $result,
                'total' => count($result)
            ]));

            return $response;

        /*} catch (\Exception $e) {
            error_log('Erro:  ' . $e->getMessage());

            $response->setContent(json_encode([
                'success' => false,
                'error' => 'Erro ao processar a requisição'
            ]));

            return $response;
        }*/
    }


    public function fetchAwakeningsPatientsAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');

        try {
            // 1. Obter e validar parâmetros
            $startDate = $this->params()->fromQuery('start_date');
            $endDate = $this->params()->fromQuery('end_date');

            if (empty($startDate) || empty($endDate)) {
                $response->setContent(json_encode([
                    'success' => false,
                    'error' => 'As datas de início e fim são obrigatórias'
                ]));
                return $response;
            }

            // Validar formato das datas
            $startDateTime = \DateTime::createFromFormat('Y-m-d', $startDate);
            $endDateTime = \DateTime::createFromFormat('Y-m-d', $endDate);

            if (!$startDateTime || !$endDateTime) {
                $response->setContent(json_encode([
                    'success' => false,
                    'error' => 'Formato de data inválido. Use YYYY-MM-DD'
                ]));
                return $response;
            }

            if ($endDateTime < $startDateTime) {
                $response->setContent(json_encode([
                    'success' => false,
                    'error' => 'A data final deve ser maior ou igual à data inicial'
                ]));
                return $response;
            }

            // Converter para DateTime completo
            $startDateTime = new \DateTime($startDate .  ' 00:00:00');
            $endDateTime = new \DateTime($endDate . ' 23:59:59');

            // 2. Buscar pacientes com despertares
            $pacienteStatusRepo = $this->entityManager->getRepository(PacienteStatus::class);
            $pacienteRepo = $this->entityManager->getRepository(Paciente::class);

            $awakeningsData = $pacienteStatusRepo->findPatientsWithAwakenings(
                $startDateTime,
                $endDateTime
            );

            if (empty($awakeningsData)) {
                $response->setContent(json_encode([
                    'success' => true,
                    'patients' => [],
                    'total' => 0,
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]));
                return $response;
            }

            // 3. Extrair IDs e criar mapa de despertares
            $pacienteIds = [];
            $awakeningsMap = [];

            foreach ($awakeningsData as $data) {
                // CORRIGIDO: usar 'pacienteId' ao invés de 'id'
                $pacienteIds[] = $data['id'];
                $awakeningsMap[$data['id']] = $data['awakenings'];
            }

            // 4. Buscar detalhes dos pacientes
            $patientsDetails = $pacienteRepo->findPatientsWithDetails($pacienteIds);

            // 5. Montar resultado completo
            $result = [];

            foreach ($patientsDetails as $patient) {
                $pacienteId = $patient['id'];

                // Buscar duração média
                $avgDuration = $pacienteStatusRepo->getAverageDuration(
                    $pacienteId,
                    $startDateTime,
                    $endDateTime
                );

                // Buscar última data que acordou
                $lastNight = $pacienteStatusRepo->getLastAwakeningDate(
                    $pacienteId,
                    $startDateTime,
                    $endDateTime
                );

                // Formatar dados
                $avgDurationFormatted = $this->formatTotalTime($avgDuration);
                $lastNightFormatted = $this->formatDate($lastNight);
                $photoUrl = $this->formatPhotoUrl($patient['photo']);

                $result[] = [
                    'id' => $pacienteId,
                    'name' => $patient['name'],
                    'room' => $patient['room'] ?? 'Sem leito',
                    'awakenings' => (int) $awakeningsMap[$pacienteId],
                    'avgDuration' => $avgDurationFormatted,
                    'lastNight' => $lastNightFormatted,
                    'photo' => $photoUrl
                ];
            }

            // 6. Ordenar por número de despertares (decrescente)
            usort($result, function($a, $b) {
                return $b['awakenings'] - $a['awakenings'];
            });

            $response->setContent(json_encode([
                'success' => true,
                'patients' => $result,
                'total' => count($result),
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ], JSON_UNESCAPED_UNICODE));

            return $response;

        } catch (\Exception $e) {
            error_log('Erro ao buscar pacientes:  ' . $e->getMessage());
            error_log($e->getTraceAsString());

            // CORRIGIDO: json_encode estava faltando
            $response->setContent(json_encode([
                'success' => false,
                'error' => 'Erro ao processar a requisição.  Por favor, tente novamente.',
                'debug' => $e->getMessage()
            ]));

            return $response;
        }
    }

    public function fetchAwakeTimeAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json; charset=utf-8');

        try {
            $startDate = $this->params()->fromQuery('start_date');
            $endDate = $this->params()->fromQuery('end_date');

            if (empty($startDate) || empty($endDate)) {
                $response->setContent(json_encode([
                    'success' => false,
                    'error' => 'As datas de início e fim são obrigatórias'
                ]));
                return $response;
            }

            $startDateTime = new \DateTime($startDate .  ' 00:00:00');
            $endDateTime = new \DateTime($endDate . ' 23:59:59');

            $pacienteStatusRepo = $this->entityManager->getRepository(PacienteStatus::class);
            $pacienteRepo = $this->entityManager->getRepository(Paciente::class);

            // Buscar pacientes com tempo total desperto
            $awakeTimeData = $pacienteStatusRepo->findPatientsWithTotalAwakeTime(
                $startDateTime,
                $endDateTime
            );

            if (empty($awakeTimeData)) {
                $response->setContent(json_encode([
                    'success' => true,
                    'patients' => [],
                    'total' => 0
                ]));
                return $response;
            }

            $pacienteIds = array_column($awakeTimeData, 'pacienteId');
            $timeMap = [];
            foreach ($awakeTimeData as $data) {
                $timeMap[$data['pacienteId']] = $data['totalMinutes'];
            }

            $patientsDetails = $pacienteRepo->findPatientsWithDetails($pacienteIds);

            $result = [];
            foreach ($patientsDetails as $patient) {
                $pacienteId = $patient['id'];
                $totalMinutes = (int) $timeMap[$pacienteId];

                $result[] = [
                    'id' => $pacienteId,
                    'name' => $patient['name'],
                    'room' => $patient['room'] ?? 'Sem leito',
                    'totalTime' => $this->formatTotalTime($totalMinutes),
                    'photo' => $this->formatPhotoUrl($patient['photo'])
                ];
            }

            usort($result, function($a, $b) use ($timeMap) {
                return $timeMap[$b['id']] - $timeMap[$a['id']];
            });

            $response->setContent(json_encode([
                'success' => true,
                'patients' => $result,
                'total' => count($result)
            ]));

            return $response;

        } catch (\Exception $e) {
            error_log('Erro:  ' . $e->getMessage());

            $response->setContent(json_encode([
                'success' => false,
                'error' => 'Erro ao processar a requisição'
            ]));

            return $response;
        }
    }

    /**
     * Formata data para exibição
     */
    private function formatDate($date)
    {

        if(is_null($date)) {
            return "N/A";
        }

        $date = new \DateTime($date);
        if (!$date) {
            return 'N/A';
        }
        return $date->format('d/m/Y');
    }

    private function formatTotalTime($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' minutos';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes == 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $remainingMinutes . 'min';
    }

    /**
     * Formata URL da foto
     */
    private function formatPhotoUrl($photo)
    {
        if (empty($photo)) {
            return '/uploads/default_img.jpeg';
        }
        return '/uploads/' . $photo;
    }

}
