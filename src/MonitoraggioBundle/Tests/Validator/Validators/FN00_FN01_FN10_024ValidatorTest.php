<?php

namespace MonitoraggioBundle\Tests\Validator\Validators;

use BaseBundle\Tests\Service\TestBaseService;
use MonitoraggioBundle\Validator\Validators\FN00_FN01_FN10_024Validator;
use MonitoraggioBundle\Validator\Constraints\FN00_FN01_FN10_024;
use Doctrine\ORM\Query;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use Doctrine\ORM\AbstractQuery;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;


class FN00_FN01_FN10_024ValidatorTest extends TestBaseService
{
	/**
	 * @var FN00_FN01_FN10_024Validator
	 */
	protected $validator;

	/**
	 * @var FN00_FN01_FN10_024
	 */
	protected $constraint;

	/**
	 * @var array|null
	 */
	protected $costoAmmesso = [null];

	/**
	 * @var array|null
	 */
	protected $finanziamento = [null];

		/**
	 * @var array|null
	 */
	protected $economie = [null];

	/**
	 * @var ExecutionContextInterface
	 */
	protected $context;

	/**
	 * @var MonitoraggioConfigurazioneEsportazioneTavole
	 */
	protected $tavola;

	
	public function setUp(){
		parent::setUp();
		$this->constraint = new FN00_FN01_FN10_024([]);
		$this->context = $this->createMock(ExecutionContextInterface::class);
		$this->validator = new FN00_FN01_FN10_024Validator($this->em);
		$this->validator->initialize($this->context);
		$query = $this->createQueryMock([null]);
		$this->em->method('createQuery')->willReturn($query);

		$esportazione = new MonitoraggioEsportazione();
		$richiesta = new Richiesta();
		$configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta($richiesta,$esportazione);
		$this->tavola = new MonitoraggioConfigurazioneEsportazioneTavole($configurazione);
		$this->tavola->setTavolaProtocollo('FN00');

	}

	/**
	 * @dataProvider validateOkDataProvider
	 */
	public function testValidateok(?float $costoAmmesso, ?float $finanziamento, ?float $economia){
		$this->costoAmmesso =[$costoAmmesso];
		$this->finanziamento = [$finanziamento];
		$this->economie = [$economia];

		$this->validator->validate($this->tavola, $this->constraint);
	}

	public function validateOkDataProvider(): array{
		return [
			[null, null, null],
			[0, 20, 10]
		];
	}

	/** TEST DISATTIVO
	 * @dataProvider validateNoOkDataProvider
	 */
	public function xtestValidateNoOk(?float $costoAmmesso, ?float $finanziamento, ?float $economia){
		$this->costoAmmesso =[$costoAmmesso];
		$this->finanziamento = [$finanziamento];
		$this->economie = [$economia];

		$constraintBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
		$this->context->expect($this->atLeastOnce())->method('buildViolation')->willReturn($constraintBuilder);

		$this->validator->validate($this->tavola, $this->constraint);
	}

	public function validateNoOkDataProvider(): array{
		return [
			[10, 10, 10]
		];
	}

	protected function createQueryMock($res): AbstractQuery{
		// $q = $this->createMock(AbstractQuery::class);
		$q =$this->getMockBuilder(AbstractQuery::class)
		->disableOriginalConstructor()
		->disableOriginalClone()
		->disableArgumentCloning()
		->disallowMockingUnknownTypes()
		->setMethods(['setMaxResults', '_doExecute', 'getSQL', 'setParameter', 'getOneOrNullResult'])
		->getMock();
		$q->method('setMaxResults')->will($this->returnSelf());
		$q->method('setParameter')->will($this->returnSelf());
		$q->method('getOneOrNullResult')->will($this->returnValueMap([
			["select 1 risultato 
            from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneErrore e
            join e.monitoraggio_configurazione_esportazione_tavole monitoraggio_configurazione_esportazione_tavole
            where monitoraggio_configurazione_esportazione_tavole = :monitoraggio_configurazione_esportazione_tavole and e.codice_errore_igrue = :codice_errore_igrue
		", NULL],
			[FN00_FN01_FN10_024Validator::$QUERY_COSTO_AMMESSO, $this->costoAmmesso],
			[FN00_FN01_FN10_024Validator::$QUERY_FINANZIAMENTO, $this->finanziamento],
			[FN00_FN01_FN10_024Validator::$QUERY_ECONOMIE, $this->economie]
		]));


		return $q;
	}
}