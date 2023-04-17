<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 21/01/16
 * Time: 17:23
 */

namespace RichiesteBundle\Service;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreRichiestaService {

	protected $container;

	/**
	 * GestoreRichiestaService constructor.
	 */
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	 * @param Procedura $procedura
	 * @return IGestoreRichiesta
	 * @throws \Exception
	 */
	public function getGestore(Procedura $procedura = null) {

		if (!is_null($procedura)) {
			$id_bando = $procedura->getId();
		} else {
			$id_bando = $this->container->get("request_stack")->getCurrentRequest()->get("id_bando");
			if (is_null($id_bando)) {
				$id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
				if (is_null($id_richiesta)) {
					throw new \Exception("Nessun id_richiesta indicato");
				}
				$richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
				if (is_null($richiesta)) {
					throw new \Exception("Nessuna richiesta trovata");
				}
				$id_bando = $richiesta->getProcedura()->getId();
			}
			$procedura = $this->container->get("doctrine")->getRepository("SfingeBundle:Procedura")->find($id_bando);
		}

		if ($procedura->isAssistenzaTecnica()) {
			return new GestoreAssistenzaTecnicaBase($this->container);
		}
		
		if ($procedura->isIngegneriaFinanziaria()) {
			return new GestoreIngegneriaFinanziariaBase($this->container);
		}
		
		if ($procedura->isAcquisizioni()) {
			return new GestoreAcquisizioniBase($this->container);
		}

		//cerco un gestore per quel bando
		$nomeClasse = "RichiesteBundle\GestoriRichieste\GestoreRichiesteBando_" . $id_bando;
		try {
			$gestoreBandoReflection = new \ReflectionClass($nomeClasse);
			return $gestoreBandoReflection->newInstance($this->container);
		} catch (\ReflectionException $ex) {
			
		}

		return new GestoreRichiestaBase($this->container);
	}

}
