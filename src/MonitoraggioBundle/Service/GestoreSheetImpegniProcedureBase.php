<?php

namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use RichiesteBundle\Entity\RichiestaRepository;
use RichiesteBundle\Entity\Richiesta;
use BaseBundle\Exception\SfingeException;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class GestoreSheetImpegniProcedureBase implements IGestoreSheetImpegniProcedure {
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Worksheet
     */
    protected $sheet;

    /**
     * @var RichiestaRepository
     */
    protected $richiestaRepository;

    	/** @var array */
	protected $warnings = [];

    public function elabora(): array {
        throw new \Exception("Metodo non implementato");
    }

    public function __construct(ContainerInterface $container, Worksheet $sheet) {
        $this->sheet = $sheet;
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->richiestaRepository = $this->em->getRepository('RichiesteBundle:Richiesta');;
    }

    protected function getValoriRiga(Row $row, string $start, int $lenght): array {
        return \array_map(function (Cell $c) {
            return $c->getValue();
        }, \array_values(
            \array_slice(
                \iterator_to_array(
                    $row->getCellIterator($start)
                ), 0, $lenght
        )));
    }

    protected function getImporto(string $importo): float{
        return \floatval(\str_replace(',','.', $importo));
    }

    protected function getData($data): ?\DateTime {
        if(\is_null($data)) {
            return null;
        }
        if (\is_numeric($data)) {
            return Date::excelToDateTimeObject($data);
        }

        $d = \DateTime::createFromFormat('d/m/Y', $data);
        if($d === false){
            throw new SfingeException('Impossibile effettuare la conversione della data');
        }
        $d->setTime(0,0);

        return $d;
    }

    protected function getRichiesta(string $codice): ?Richiesta{
        return $this->richiestaRepository->findByProtocollo($codice);
    }

    protected function addWarning(string $msg):void{
        $this->warnings[] = $msg;
    }

    public function getWarnings(): array{
        return $this->warnings;
    }
}
