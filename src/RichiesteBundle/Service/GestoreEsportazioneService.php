<?php

namespace RichiesteBundle\Service;

use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GestoreEsportazioneService {

	protected $container;

	/**
	 * GestoreRichiestaService constructor.
	 */
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	/**
	 * @param Procedura $procedura
	 * @return IGestoreEsportazione
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

		//cerco un gestore per quel bando
		$nomeClasse = "RichiesteBundle\GestoriEsportazione\GestoreEsportazioneBando_" . $id_bando;
		try {
			$gestoreBandoReflection = new \ReflectionClass($nomeClasse);
			return $gestoreBandoReflection->newInstance($this->container, $procedura);
		} catch (\ReflectionException $ex) {

		}

		return new GestoreEsportazioneBase($this->container, $procedura);
	}

}
