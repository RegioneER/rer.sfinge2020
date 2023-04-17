<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseServiceTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use BaseBundle\Service\SpreadsheetFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use MonitoraggioBundle\Utils\StringWrapper;

class EstrazioneExcelIgrueService {
    use BaseServiceTrait;
    /** @var array */
    protected $controlli;

    /**
     * @var Spreadsheet
     */
    protected $excel;

    public function __construct(ContainerInterface $container, array $controlli) {
        $this->container = $container;
        $this->controlli = $controlli;

        /** @var SpreadsheetFactory $excelService */
        $excelService = $this->container->get('phpoffice.spreadsheet');
        $this->excel = $excelService->getSpreadSheet();
    }

    public function execute(): Spreadsheet {
        $sheet = $this->excel->getActiveSheet();
        $this->impostaTitoli($sheet);
        $stmt = $this->getStatement();
        $riga = 1;
        while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
            $normalizedRow = $this->normalize($row);
            ++$riga;
            $sheet->fromArray($normalizedRow, null, "A$riga");
        }
        return $this->excel;
    }

    public function normalize(array $row): array {
        $res = \array_map(
            function ($key, $value) {
                if($key == 0){
                    return new StringWrapper($value);
                }
                if($value){
                    return 'SI';
                }
                return 'NO';
            },
            \array_keys($row),
            \array_values($row)
        );

        return $res;
    }

    private function impostaTitoli(Worksheet $sheet) {
        $sheet->fromArray(\array_merge(['Codice locale progetto'],
        \array_keys($this->controlli)), null, 'A1');
    }

    private function getStatement() {
        $colonne = \array_map(function (string $nome) {
            return "controllo_igrue(richiesta.id, '$nome') AS c$nome";
        }, \array_keys($this->controlli));
        $colonne_unite = implode(', ', $colonne);

        $query = "SELECT COALESCE(CONCAT(protocollo.registro_pg, '/', protocollo.anno_pg,'/', protocollo.num_pg), richiesta.id) AS p, "
        . $colonne_unite .
       " FROM richieste AS richiesta
        INNER JOIN attuazione_controllo_richieste as atc on atc.richiesta_id = richiesta.id
        INNER JOIN richieste_protocollo AS protocollo ON protocollo.richiesta_id = richiesta.id
        AND protocollo.data_cancellazione IS NULL
        AND protocollo.tipo = 'FINANZIAMENTO'
        WHERE richiesta.data_cancellazione IS NULL
        AND richiesta.flag_por = 1";

        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        return $em->getConnection()->executeQuery($query);
    }
}
