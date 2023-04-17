<?php

namespace AttuazioneControlloBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;

class MostraValidazioneIntegrazioneTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'base_mostra_validazione_integrazione';
    }

    public function mostraValidazione($integrazione, $sezione, $path, $label, $proponente = null){

        $esito = $this->trovaEsito($integrazione, $sezione, $proponente);

        return $this->container->get("templating")->render("IstruttorieBundle:RispostaIntegrazione:mostraValidazione.html.twig", array("esito" => $esito, "path"=>$path, "label"=>$label));
    }

    private function trovaEsito($integrazione, $sezione, $proponente = null){
		$bundle = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get("gestore_integrazione_pagamento_bundle");

        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'nota_risposta':
                $esito = $this->container->get("gestore_integrazione_pagamento")->getGestore($bundle)->validaNotaRisposta($integrazione);
                break;
            case 'documenti_richiesta':
                $esito = $this->container->get("gestore_integrazione_pagamento")->getGestore($bundle)->validaDocumenti($integrazione, $proponente);
                break;			
            default:
                # code...
                break;
        }

        return $esito;
    }

    public function getFunctions()
    {
        return array(
			new \Twig_SimpleFunction('mostra_validazione_risposta_integrazione', array($this, 'mostraValidazione'), array('is_safe' => array('html'))),

        );
    }
}