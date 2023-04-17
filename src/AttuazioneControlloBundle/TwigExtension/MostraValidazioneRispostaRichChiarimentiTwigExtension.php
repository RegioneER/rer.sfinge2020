<?php

namespace AttuazioneControlloBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Utility\EsitoValidazione;

class MostraValidazioneRispostaRichChiarimentiTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'base_mostra_validazione_risposta_rich_chiarimenti';
    }

    public function mostraValidazione($risposta_richiesta_chiarimenti, $sezione, $path, $label, $proponente = null){

        $esito = $this->trovaEsito($risposta_richiesta_chiarimenti, $sezione, $proponente);

        return $this->container->get("templating")->render("IstruttorieBundle:RispostaRichiestaChiarimenti:mostraValidazione.html.twig", array("esito" => $esito, "path"=>$path, "label"=>$label));
    }

    private function trovaEsito($risposta_richiesta_chiarimenti, $sezione, $proponente = null){
		$bundle = $this->container->get('request_stack')->getCurrentRequest()->getSession()->get("gestore_richiesta_chiarimenti_bundle");

        $esito = new EsitoValidazione(true);
        switch ($sezione) {
            case 'nota_risposta':
                $esito = $this->container->get("gestore_richieste_chiarimenti")->getGestore($bundle)->validaNotaRisposta($risposta_richiesta_chiarimenti);
                break;
            case 'documenti_richiesta_chiarimenti':
                $esito = $this->container->get("gestore_richieste_chiarimenti")->getGestore($bundle)->validaDocumenti($risposta_richiesta_chiarimenti, $proponente);
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
			new \Twig_SimpleFunction('mostra_validazione_risposta_rich_chiarimenti', array($this, 'mostraValidazione'), array('is_safe' => array('html'))),

        );
    }
}