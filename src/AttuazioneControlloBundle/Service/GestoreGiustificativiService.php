<?php

namespace AttuazioneControlloBundle\Service;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Service\IGestoreRichiesta;

class GestoreGiustificativiService {

	protected $container;

	/**
	 * GestoreGiustificativiService constructor.
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
			$id_procedura = $procedura->getId();
		} else {
			$id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->getSession()->get("id_richiesta");
			if (is_null($id_richiesta)) {
				throw new \Exception("Nessun id_richiesta indicato");
			}
			$richiesta = $this->container->get("doctrine")->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
			if (is_null($richiesta)) {
				throw new \Exception("Nessuna richiesta trovata");
			}
			$id_procedura = $richiesta->getProcedura()->getId();
			
			$procedura = $this->container->get("doctrine")->getRepository("SfingeBundle:Procedura")->find($id_procedura);
		}

		if ($procedura->isAssistenzaTecnica() == true ) {
			return new GestoreGiustificativiAssTecnicaBase($this->container);
		}
		
		if ($procedura->isIngegneriaFinanziaria() == true ) {
			return new GestoreGiustificativiIngFinanziariaBase($this->container);
		}
		
		if ($procedura->isAcquisizioni() == true ) {
			return new GestoreGiustificativiAcquisizioniBase($this->container);
		}
		
		//cerco un gestore per quel bando
		$nomeClasse = "AttuazioneControlloBundle\GestoriGiustificativi\GestoreGiustificativiBando_" . $id_procedura;
		try {
			$gestoreBandoReflection = new \ReflectionClass($nomeClasse);
			return $gestoreBandoReflection->newInstance($this->container);
		} catch (\ReflectionException $ex) {

		}

		return new GestoreGiustificativiBase($this->container);
	}

}
