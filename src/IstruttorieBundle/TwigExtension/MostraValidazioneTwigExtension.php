<?php

namespace IstruttorieBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;

class MostraValidazioneTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'base_mostra_validazione_integrazione_istr';
    }

    public function mostraValidazione($integrazione, $sezione, $path, $label, $proponente = null){

        $esito = $this->trovaEsito($integrazione, $sezione, $proponente);

        return $this->container->get("templating")->render("IstruttorieBundle:RispostaIntegrazione:mostraValidazione.html.twig", array("esito" => $esito, "path"=>$path, "label"=>$label));
    }
	
	public function mostraValidazioneRispostaComunicazione($comunicazione, $sezione, $path, $label, $proponente = null){

        $esito = $this->trovaEsitoComunicazione($comunicazione, $sezione, $proponente);

        return $this->container->get("templating")->render("IstruttorieBundle:RispostaComunicazione:mostraValidazione.html.twig", array("esito" => $esito, "path"=>$path, "label"=>$label));
    }

    private function trovaEsito($integrazione, $sezione, $proponente = null){
		$bundle = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get("gestore_integrazione_bundle");

        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'nota_risposta':
                $esito = $this->container->get("gestore_integrazione")->getGestore($bundle)->validaNotaRisposta($integrazione);
                break;
            case 'documenti_richiesta':
                $esito = $this->container->get("gestore_integrazione")->getGestore($bundle)->validaDocumenti($integrazione, $proponente);
                break;			
            default:
                break;
        }

        return $esito;
    }
	
	private function trovaEsitoComunicazione($comunicazione, $sezione, $proponente = null){
		$bundle = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get("gestore_comunicazione_bundle");

        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'nota_risposta_comunicazione':
                $esito = $this->container->get("gestore_comunicazione")->getGestore($bundle)->validaNotaRisposta($comunicazione);
                break;	
			case 'documenti_richiesta':
                $esito = $this->container->get("gestore_comunicazione")->getGestore($bundle)->validaDocumenti($comunicazione, $proponente);
                break;
            default:
                break;
        }

        return $esito;
    }

    public function getFunctions()
    {
        return array(
			new \Twig_SimpleFunction('mostra_validazione_risposta_integrazione_istr', array($this, 'mostraValidazione'), array('is_safe' => array('html'))),
			new \Twig_SimpleFunction('mostra_validazione_risposta_comunicazione_istr', array($this, 'mostraValidazioneRispostaComunicazione'), array('is_safe' => array('html'))),

        );
    }
}