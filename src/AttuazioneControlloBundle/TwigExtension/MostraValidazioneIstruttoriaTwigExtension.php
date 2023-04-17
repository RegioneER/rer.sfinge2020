<?php

namespace AttuazioneControlloBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MostraValidazioneIstruttoriaTwigExtension extends AbstractExtension {

	private $container;

	function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getName() {
		return 'mostra_validazione_istruttoria';
	}

	public function mostraValidazioneIstruttoria($sezione, $path, $label, $pagamento) {

		$esito = $this->trovaEsito($sezione, $pagamento);

		return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneAttuazione.html.twig", array("esito" => $esito, "path" => $path, "label" => $label));
	}


	public function mostraValidazioneInLineIstruttoria($sezione, $path, $label, $pagamento = null, $indice_istanza_fascicolo = null) {

		$esito = $this->trovaEsito($sezione, $pagamento, $indice_istanza_fascicolo);

		return $this->container->get("templating")->render("AttuazioneControlloBundle:Pagamenti:mostraValidazioneInLineAttuazione.html.twig", array("sezione" => $sezione, "pagamento" => $pagamento, "esito" => $esito, "path" => $path, "label" => $label));
	}

	public function mostraValidazioneGiustificativoColonnaIstruttoria($sezione, $path, $label, $giustificativo) {

		$esito = $this->trovaEsitoIstruttoria($sezione, $giustificativo);

		return $esito;
	}
	
	private function trovaEsitoIstruttoria($sezione, $giustificativo) {

		$esito = new EsitoValidazione(true);
		$procedura = $giustificativo->getPagamento()->getProcedura();
		switch ($sezione) {

			case 'documenti_giustificativo_istruttoria':
				$esito = $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->validaDocumentiIstruttoria($giustificativo);
				break;
			case 'imputazioni_giustificativo_istruttoria':
				$esito = $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->validaImputazioniGiustificativoIstruttoria($giustificativo);
				//$esito->setMessaggio("TEST");
				break;
			case 'completa_giustificativo_istruttoria':
				$esito = $this->container->get("gestore_giustificativi_istruttoria")->getGestore($procedura)->validaCompletaGiustificativoIstruttoria($giustificativo);
				break;			
			
			default:
				# code...
				break;
		}
		
		return $esito;
	}

	private function trovaEsito($sezione, $pagamento, $indiceIstanzaFascicolo = null) {

		$esito = new EsitoValidazione(true);
		$gestore_pagamenti = $this->container->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura());
		//$gestore_giustificativi = $this->container->get("gestore_giustificativi_istruttoria")->getGestore($pagamento->getProcedura());
		switch ($sezione) {
			case 'avanzamento_istruttoria':
				$esito = $gestore_pagamenti->validaGiustificativiIstruttoria($pagamento);
				break;
			case 'avanzamento_multi_istruttoria':
				$esito = $gestore_pagamenti->validaGiustificativiIstruttoria($pagamento);
				break;
			case 'ricercatori_istruttoria':
				$esito = $gestore_pagamenti->validaIstruttoriaRicercatori($pagamento);
				break;	
			case 'relazione_tecnica':
				$esito = $gestore_pagamenti->validaRelazioneTecnica($pagamento);
				break;				
			case 'antimafia_casellario':
				$esito = $gestore_pagamenti->validaAntimafiaCasellario($pagamento);
				break;
			case 'dati_durc':
				$esito = $gestore_pagamenti->validaDatiDurc($pagamento);
				break;
			case 'dati_bancari':
				$esito = $gestore_pagamenti->validaIstruttoriaDatibancari($pagamento);
				break;	
			case 'monitoraggio_dichiarazioni':
				$esito = $gestore_pagamenti->validaIstruttoriaMonitoraggioDichiarazioni($pagamento);
				break;	
			case 'documenti_generali_pagamento':
				$esito = $gestore_pagamenti->validaIstruttoriaDocumentiGenerali($pagamento);
				break;
			case 'documenti_dichiarazioni_rendicontazione':
				$esito = $gestore_pagamenti->validaDichiarazioniProponenti($pagamento);
				break;	
			case 'giustificativi':
				$esito = $gestore_pagamenti->validaGiustificativiIstruttoria($pagamento);
				break;
			case 'incremento_occupazionale':
				if ($pagamento->getProcedura()->getId() == 7) {
					$esito = $gestore_pagamenti->validaIncrementoOccupazionale($pagamento);
				} else {
					$gestore_pagamenti = $this->container->get("gestore_incremento_occupazionale_istruttoria")->getGestore($pagamento->getProcedura());
					$esito = $gestore_pagamenti->validaIncrementoOccupazionale($pagamento);
				}
				break;
			case 'relazione_finale_a_saldo':
				$esito = $gestore_pagamenti->validaRelazioneFinaleSaldo($pagamento);
				break;			
			case 'documenti_progetto':
				$esito = $gestore_pagamenti->validaDocumentiProgetto($pagamento);
				break;
			case 'avanzamento_piano_costi':
				$esito = $gestore_pagamenti->validaAvanzamentoPianoCosti($pagamento);
				break;
			case 'indicatori_output':
				$esito = $gestore_pagamenti->validaIndicatoriOutput($pagamento);
				break;
                        case 'contratti':
				$esito = $gestore_pagamenti->validaContrattiIstruttoria($pagamento);
				break;
			default:
				# code...
				break;
		}

		return $esito;
	}

	public function getFunctions() {
		return array(
			new TwigFunction('mostra_validazione_istruttoria', array($this, 'mostraValidazioneIstruttoria'), array('is_safe' => array('html'))),
			new TwigFunction('mostra_validazione_in_line_istruttoria', array($this, 'mostraValidazioneInLineIstruttoria'), array('is_safe' => array('html'))),
			new TwigFunction('mostra_validazione_giustificativo_colonna_istruttoria', array($this, 'mostraValidazioneGiustificativoColonnaIstruttoria'), array('is_safe' => array('html'))),
		);
	}

}
