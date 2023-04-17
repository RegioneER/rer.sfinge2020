<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use PaginaBundle\Services\Pagina;
use FascicoloBundle\Entity\IstanzaFascicolo;
use FascicoloBundle\Entity\IstanzaPagina;
use RichiesteBundle\Entity\OggettoRichiesta;

class Questionario extends ASezioneRichiesta {

	const TITOLO = 'Questionario';
	const SOTTOTITOLO = 'Questionario';
	const VALIDATION_GROUP = 'questionario';
	const NOME_SEZIONE = 'questionario';
	const MSG_QUESTIONARIO_NON_COMPILATO = 'Questionario non compilato';

	/**
	 * @var string[]
	 */
	protected $validations_groups = array();

	/**
	 * {@inheritDoc}
	 */
	public function getTitolo() {
		return self::TITOLO;
	}

	/**
	 * @{@inheritDoc}
	 */
	public function valida() {
		if ($this->isQuestionarioNonPresente()) {
			$this->listaMessaggi[] = self::MSG_QUESTIONARIO_NON_COMPILATO;
			return false;
		}
		$esito = $this->container->get("fascicolo.istanza")->validaIstanzaPagina($this->getIstanzaFascicolo()->getIndice());
		foreach ($esito->getTuttiMessaggi() as $messaggio) {
			$this->listaMessaggi[] = $messaggio;
		}
		return false;
	}

	private function isQuestionarioNonPresente() {
		$istanzaFascicolo = $this->getIstanzaFascicolo();
		return \is_null($istanzaFascicolo) || \is_null($istanzaFascicolo->getIndice());
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUrl() {
		$istanzaFascicolo = $this->getIstanzaFascicoloOrIstanziaNuova();
		return $this->generateUrl(self::ROTTA, array(
					'id_richiesta' => $this->richiesta->getId(),
					'nome_sezione' => self::NOME_SEZIONE,
					'parametro1' => $istanzaFascicolo->getId(),
		));
	}

	protected function getIstanzaFascicoloOrIstanziaNuova() : IstanzaFascicolo{
		$oggetto = $this->getOggettoRichiesta();
		if(!$oggetto){
			$oggetto = new OggettoRichiesta($this->richiesta);
			$this->richiesta->addOggettiRichiestum($oggetto);
		}
		$istanzaFascicolo = $oggetto->getIstanzaFascicolo();
		if (\is_null($istanzaFascicolo)) {
			$em = $this->getEm();
			$istanzaFascicolo = new IstanzaFascicolo();
			$fascicoloProcedura = $this->richiesta->getProcedura()->getFascicoliProcedura()->first();
			if(!$fascicoloProcedura){
				throw new \Exception('Non sono stati definiti fascicoli per la procedura');
			}
			$fascicolo = $fascicoloProcedura->getFascicolo();
			$istanzaFascicolo->setFascicolo($fascicolo); //TODO decidere logica di scelta del fascicolo

			$indice = new IstanzaPagina();
			$indice->setPagina($fascicolo->getIndice());
			$istanzaFascicolo->setIndice($indice);

			$oggetto->setIstanzaFascicolo($istanzaFascicolo);

			$em->persist($istanzaFascicolo);
			$em->persist($oggetto);
			$em->flush();
		}
		return $istanzaFascicolo;
	}
	/**
	 * {@inheritDoc}
	 */
	public function visualizzaSezione(array $parametri) {
		$istanzaFascicolo = $this->getIstanzaFascicoloOrIstanziaNuova();

		//id_istanza_pagina
		return $this->redirectToRoute('questionario_richiesta_pa', array(
					'id_istanza_pagina' => $istanzaFascicolo->getIndice()->getId(),
					'azione' => $this->riepilogo->isRichiestaDisabilitata() ? 'visualizza' : 'modifica',
		));
	}

	/**
	 * @return \RichiesteBundle\Entity\OggettoRichiesta
	 */
	public function getOggettoRichiesta() {
		return $this->richiesta->getOggettiRichiesta()->first();
	}

	/**
	 * @return IstanzaFascicolo
	 */
	protected function getIstanzaFascicolo() {
		if ($this->getOggettoRichiesta() != false) {
			return $this->getOggettoRichiesta()->getIstanzaFascicolo();
		} else {
			return null;
		}
	}

}
