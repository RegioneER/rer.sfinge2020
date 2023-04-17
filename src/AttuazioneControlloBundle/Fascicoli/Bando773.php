<?php

namespace AttuazioneControlloBundle\Fascicoli;

class Bando773 {

	protected $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function hasAppendiceBVisibile($istanzaFascicolo) {
		$em = $this->container->get("doctrine")->getManager();
		$pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array("istanza_fascicolo" => $istanzaFascicolo->getId()));

		if ($pagamento->getModalitaPagamento()->getCodice() != 'SAL') {
			return false;
		}

		$proponente = $pagamento->getRichiesta()->getMandatario();
		$priorita = $proponente->getPriorita();

		$sistemi = array();
		$orientamenti = array();
		$sistemiVerifica = array('A.1', 'A.2', 'A.3', 'B.2');
		$orientamentiVerificaAgro = array('A.1.1', 'A.1.3', 'A.1.5');
		$orientamentiVerificaEdil = array('A.2.1', 'A.2.3', 'A.2.4', 'A.2.5');
		$orientamentiVerificaMecc = array('A.3.3');
		$orientamentiVerificaCull = array('B.2.2');

		foreach ($priorita as $prioritaS) {
			$sistemi[] = $prioritaS->getSistemaProduttivo()->getCodice();
			$orientamenti[] = $prioritaS->getOrientamentoTematico()->getCodice();
		}
		foreach ($sistemi as $sistema) {
			if (in_array($sistema, $sistemiVerifica)) {
				if ($sistema == 'A.1') {
					foreach ($orientamenti as $orientamento) {
						if (in_array($orientamento, $orientamentiVerificaAgro)) {
							return true;
						}
					}
				}
				if ($sistema == 'A.2') {
					foreach ($orientamenti as $orientamento) {
						if (in_array($orientamento, $orientamentiVerificaEdil)) {
							return true;
						}
					}
				}
				if ($sistema == 'A.3') {
					foreach ($orientamenti as $orientamento) {
						if (in_array($orientamento, $orientamentiVerificaMecc)) {
							return true;
						}
					}
				}
				if ($sistema == 'B.2') {
					foreach ($orientamenti as $orientamento) {
						if (in_array($orientamento, $orientamentiVerificaCull)) {
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	public function isSingolaImpresa($istanzaFascicolo) {
		$em = $this->container->get("doctrine")->getManager();
		$pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array("istanza_fascicolo" => $istanzaFascicolo->getId()));

		if ($pagamento->getModalitaPagamento()->getCodice() != 'SAL') {
			return false;
		}

		$richiesta = $pagamento->getRichiesta();
		
		if(count($richiesta->getProponenti()) == 1 ) {
			return true;
		}
		return false;
	}
	
	public function isRete($istanzaFascicolo) {
		$em = $this->container->get("doctrine")->getManager();
		$pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array("istanza_fascicolo" => $istanzaFascicolo->getId()));

		if ($pagamento->getModalitaPagamento()->getCodice() != 'SAL') {
			return false;
		}

		$richiesta = $pagamento->getRichiesta();
		
		if(count($richiesta->getProponenti()) > 1 ) {
			return true;
		}
		return false;
	}

	public function isSingolaImpresaSaldo($istanzaFascicolo) {
		$em = $this->container->get("doctrine")->getManager();
		$pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array("istanza_fascicolo" => $istanzaFascicolo->getId()));
		$richiesta = $pagamento->getRichiesta();
		return (($pagamento->getModalitaPagamento()->getCodice() == \AttuazioneControlloBundle\Entity\ModalitaPagamento::SALDO_FINALE) && (count($richiesta->getProponenti()) == 1 ));
	}

	public function isReteSaldo($istanzaFascicolo) {
		$em = $this->container->get("doctrine")->getManager();
		$pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\Pagamento")->findOneBy(array("istanza_fascicolo" => $istanzaFascicolo->getId()));
		$richiesta = $pagamento->getRichiesta();
		return (($pagamento->getModalitaPagamento()->getCodice() == \AttuazioneControlloBundle\Entity\ModalitaPagamento::SALDO_FINALE) && (count($richiesta->getProponenti()) > 1 ));
	}	
	
}
