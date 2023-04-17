<?php

namespace MonitoraggioBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use MonitoraggioBundle\Entity\PG00ProcedureAggiudicazione;
use MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG;

class PG00ProcedureAggiudicazioneTest extends TestCase {
    /**
     * @var PG00ProcedureAggiudicazione
     */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->entity = new PG00ProcedureAggiudicazione();
    }

    public function testGetTracciato() {
        $tc22 = new \MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG();
        $tc22->setMotivoAssenzaCig('setCodProgramma');

        $tc23 = new \MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione();
        $tc23->setTipoProcAgg('setSpecificaStato');

        $data = new \DateTime();

        $this->entity
            ->setCodLocaleProgetto('setCodLocaleProgetto')
            ->setCodProcAgg('setCodProcAgg')
            ->setCig('cig')
            ->setTc22MotivoAssenzaCig($tc22)
            ->setDescrProceduraAgg('setDescrProceduraAgg')
            ->setTc23TipoProceduraAggiudicazione($tc23)
            ->setImportoProceduraAgg(888)
            ->setDataPubblicazione($data)
            ->setDataAggiudicazione($data)
            ->setImportoAggiudicato(999)
            ->setFlgCancellazione('S');

        $tracciato = $this->entity->getTracciato();
        $this->assertNotNull($tracciato);

        $match = [];
        preg_match_all('/(?<=\||^)([^\|]*)(?=\||$)/', $tracciato, $match);

        $this->assertEquals(\count($match[0]), 11);
        $this->assertEquals($match[0][0], 'setCodLocaleProgetto');
        $this->assertEquals($match[0][1], 'setCodProcAgg');
        $this->assertEquals($match[0][2], 'cig');
        $this->assertEquals($match[0][3], 'setCodProgramma');
        $this->assertEquals($match[0][4], 'setDescrProceduraAgg');
        $this->assertEquals($match[0][5], 'setSpecificaStato');
        $this->assertSame($match[0][6], '888,00');
        $this->assertEquals($match[0][7], $data->format('d/m/Y'));
        $this->assertSame($match[0][8], '999,00');
        $this->assertEquals($match[0][9], $data->format('d/m/Y'));
        $this->assertEquals($match[0][10], 'S');
    }

    public function testId() {
        $this->assertNull($this->entity->getId());
    }

    public function testTipoProceduraObbligatorioSeCIGDiversoDa9999() {
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');
        $this->entity->validationCallback($context);

        $this->entity->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());
        $this->entity->validationCallback($context);
    }

    public function testValidazioneOkCup9999() {
        $this->entity->setCig(9999)
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDescrProceduraAgg('descrizione procedura')
        ->setImportoProceduraAgg(9999)
        ->setDataPubblicazione(new \DateTime())
        ->setImportoAggiudicato(999)
        ->setDataAggiudicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())->method('buildViolation');
        $this->entity->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());
        $this->entity->validationCallback($context);
    }

    public function testTipoProceduraObbligatorioSeCIGUgualeDa9999() {
        $this->entity->setCig(9999)
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDescrProceduraAgg('descrizione procedura')
        ->setImportoProceduraAgg(9999)
        ->setDataPubblicazione(new \DateTime())
        ->setImportoAggiudicato(999)
        ->setDataAggiudicazione(new \DateTime());

        $context = $this->getContextSingolaViolazione('tc23_tipo_procedura_aggiudicazione', 'Tipo procedura aggiudicazione obbligatorio');

        $this->entity->validationCallback($context);
    }

    protected function getContextSingolaViolazione(string $path, string $messaggio) {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder
        ->expects($this->once())
        ->method('atPath')
        ->with($path)
        ->willReturn($violationBuilder);

        $violationBuilder
        ->expects($this->once())
        ->method('addViolation');

        $context = $this->createMock(ExecutionContextInterface::class);
        $context
        ->expects($this->once())
        ->method('buildViolation')
        ->with($this->equalTo($messaggio))
        ->willReturn($violationBuilder);

        return $context;
    }

    public function testMotivoAssenzaCIG9999() {
        $this->entity->setCig(9999)
        ->setDescrProceduraAgg('descrizione procedura')
        ->setImportoProceduraAgg(9999)
        ->setDataPubblicazione(new \DateTime())
        ->setImportoAggiudicato(999)
        ->setDataAggiudicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->getContextSingolaViolazione('tc22_motivo_assenza_cig', 'Motivo assenza cig obbligatorio');

        $this->entity->validationCallback($context);
    }

    public function testDescrizioneAssenteCIG9999() {
        $this->entity->setCig(9999)
        ->setImportoProceduraAgg(9999)
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDataPubblicazione(new \DateTime())
        ->setImportoAggiudicato(999)
        ->setDataAggiudicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->getContextSingolaViolazione('descr_procedura_agg', 'Descrizione procedura aggiudicazione obbligatoria');

        $this->entity->validationCallback($context);
    }

    public function testImportoProceduraAggCIG9999() {
        $this->entity->setCig(9999)
        ->setImportoAggiudicato(999)
        ->setDescrProceduraAgg('descrizione procedura')
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDataPubblicazione(new \DateTime())
        ->setDataAggiudicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->getContextSingolaViolazione('importo_procedura_agg', 'Importo procedura aggiudicazione obbligatorio');

        $this->entity->validationCallback($context);
    }

    public function testDataPubblicazioneCIG9999() {
        $this->entity->setCig(9999)
        ->setImportoAggiudicato(999)
        ->setImportoProceduraAgg(9999)
        ->setDescrProceduraAgg('descrizione procedura')
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDataAggiudicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->getContextSingolaViolazione('data_pubblicazione', 'Data pubblicazione obbligatoria');

        $this->entity->validationCallback($context);
    }

    public function testImportoAggiudicatoCIG9999() {
        $this->entity->setCig(9999)
        ->setImportoProceduraAgg(9999)
        ->setDescrProceduraAgg('descrizione procedura')
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDataPubblicazione(new \DateTime())
        ->setDataAggiudicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->getContextSingolaViolazione('importo_aggiudicato', 'Importo aggiudicato obbligatorio');

        $this->entity->validationCallback($context);
    }

    public function testDataAggiudicazioneMancanteCIG9999() {
        $this->entity->setCig(9999)
        ->setImportoAggiudicato(999)
        ->setImportoProceduraAgg(9999)
        ->setDescrProceduraAgg('descrizione procedura')
        ->setTc22MotivoAssenzaCig(new TC22MotivoAssenzaCIG())
        ->setDataPubblicazione(new \DateTime())
        ->setTc23TipoProceduraAggiudicazione(new TC23TipoProceduraAggiudicazione());

        $context = $this->getContextSingolaViolazione('data_aggiudicazione', 'Data aggiudicazione obbligatoria');

        $this->entity->validationCallback($context);
    }
}
