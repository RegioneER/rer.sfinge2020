<?php

namespace AttuazioneControlloBundle\TwigExtension;

use BaseBundle\Controller\BaseController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;

class MostraValidazioneGiustificativoTwigExtension extends \Twig_Extension {

	private $container;

	function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function getName() {
		return 'mostra_validazione_giutificativo';
	}

	public function mostraValidazioneGiustificativo($sezione, $path, $label, $giustificativo) {

		$esito = $this->trovaEsito($sezione, $giustificativo);

		return $this->container->get("templating")->render("AttuazioneControlloBundle:Giustificativi:mostraValidazioneGiustificativo.html.twig", array("esito" => $esito, "path" => $path, "label" => $label));
	}

	public function mostraValidazioneGiustificativoColonna($sezione, $path, $label, $giustificativo) {

		$esito = $this->trovaEsito($sezione, $giustificativo);
		
		return $esito;
	}
	
	public function mostraValidazioneAmministrativoColonna($sezione, $path, $label, $amministrativo) {

		$esito = $this->trovaEsitoAmministrativo($sezione, $amministrativo);
		
		return $esito;
	}

	public function mostraValidazioneGiustificativoAssistenza($sezione, $path, $label, $giustificativo) {

		$esito = $this->trovaEsito($sezione, $giustificativo);

		return $this->container->get("templating")->render("AttuazioneControlloBundle:Giustificativi:mostraValidazioneGiustificativo.html.twig", array("esito" => $esito, "path" => $path, "label" => $label));
	}

	private function trovaEsito($sezione, $giustificativo) {

		$esito = new EsitoValidazione(true);
		switch ($sezione) {

			case 'giustificativo':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaGiustificativo($giustificativo);
				break;
			case 'giustificativo_particolare':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaGiustificativo($giustificativo);
				break;
			case 'documenti_giustificativo':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaDocumenti($giustificativo);
				break;
			case 'giustificativo_colonna':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaVociSpesaGiustificativo($giustificativo);
				break;
			default:
				# code...
				break;
		}

		return $esito;
	}
	
	private function trovaEsitoAmministrativo($sezione, $amministrativo) {

		$esito = new EsitoValidazione(true);
		switch ($sezione) {

			case 'documento_amministrativo':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaDocumentoAmministrativo($amministrativo);
				break;
			case 'documento_amministrativo6':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaDocumentoAmministrativo6($amministrativo);
				break;
			case 'documento_amministrativo_prototipo':
				$esito = $this->container->get("gestore_giustificativi")->getGestore()->validaDocumentoAmministrativoPrototipo($amministrativo);
				break;            
			default:
				# code...
				break;
		}

		return $esito;
	}

	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('mostra_validazione_giustificativo', array($this, 'mostraValidazioneGiustificativo'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('mostra_validazione_giustificativo_colonna', array($this, 'mostraValidazioneGiustificativoColonna'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('mostra_validazione_amministrativo_colonna', array($this, 'mostraValidazioneAmministrativoColonna'), array('is_safe' => array('html'))),			
			new \Twig_SimpleFunction('mostra_validazione_giustificativo_assistenza', array($this, 'mostraValidazioneGiustificativoAssistenza'), array('is_safe' => array('html'))),
		);
	}

}
