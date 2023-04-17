<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EstrazioneStruttura;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture\AP00;
use BaseBundle\Service\SpreadsheetFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;

class AP00Test extends TestBaseService {
    /**
     * @var AP00
     */
    private $ap00;

    /**
     * @var SpreadsheetFactory
     */
    private $spreadSheetService;

    private $excel;

    public function setUp() {
        parent::setUp();

        $this->spreadSheetService = $this->createMock(SpreadsheetFactory::class);
        $this->excel = new Spreadsheet();
        $this->spreadSheetService->method('getSpreadSheet')->willReturn($this->excel);
        $this->container->set('phpoffice.spreadsheet', $this->spreadSheetService);

        $this->ap00 = new AP00($this->container);
    }

    public function test() {
        $query = $this->createMock(AbstractQuery::class);
        $this->em->method('createQuery')->willReturn($query);
        $query->expects($this->once())->method('getResult')->willReturn([]);

		$res = $this->ap00->generateResult();
		
        $this->assertNotNull($res);
        $this->assertInstanceOf(Response::class, $res);
    }
}
