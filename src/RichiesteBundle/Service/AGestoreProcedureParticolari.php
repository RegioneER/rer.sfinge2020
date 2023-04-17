<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 22/01/16
 * Time: 09:24
 */

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use BaseBundle\Service\BaseService;
use RichiesteBundle\Entity\Richiesta;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Session\Session;

abstract class AGestoreProcedureParticolari extends BaseService implements IGestoreProcedureParticolari {

	/**
	 * @return Session
	 */
	public function getSession() {
		$session = $this->container->get('request_stack')->getCurrentRequest()->getSession();
		return $session;
	}

	public function getCurrentRequest() {
		$request = $this->container->get('request_stack')->getCurrentRequest();
		return $request;
	}

	/**
	 * @return Procedura
	 * @throws SfingeException
	 */
	public function getProcedura() {
		$id_bando = $this->container->get("request_stack")->getCurrentRequest()->get("id_bando");
		if (is_null($id_bando)) {
			$id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
			if (is_null($id_richiesta)) {
				throw new SfingeException("Nessun id_richiesta indicato");
			}
			$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
			if (is_null($richiesta)) {
				throw new SfingeException("Nessuna richiesta trovata");
			}
			return $richiesta->getProcedura();
		}
		throw new SfingeException("Nessuna richiesta trovata");
	}

	/**
	 * @return Richiesta
	 * @throws SfingeException
	 */
	public function getRichiesta() {
		$id_richiesta = $this->container->get("request_stack")->getCurrentRequest()->get("id_richiesta");
		if (is_null($id_richiesta)) {
			throw new SfingeException("Id richiesta non trovata");
		}
		$richiesta = $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta);
		if (is_null($richiesta)) {
			throw new SfingeException("Richiesta non trovata");
		}

		return $richiesta;
	}

}
