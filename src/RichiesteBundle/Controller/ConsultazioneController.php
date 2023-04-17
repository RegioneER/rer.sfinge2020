<?php

namespace RichiesteBundle\Controller;

use BaseBundle\Controller\BaseController;
use RichiesteBundle\Form\Entity\RicercaBandoManifestazione;
use SfingeBundle\Entity\ManifestazioneInteresse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;

/**
 * @Route("/consultazione")
 */
class ConsultazioneController extends BaseController {
    /**
     * @Route("/elenco_bandi/{sort}/{direction}/{page}", defaults={"sort" : "s.id", "direction" : "asc", "page" : "1"}, name="elenco_bandi")
     * @Method({"GET", "POST"})
     * @Template("RichiesteBundle:Richieste:elencoBandi.html.twig")
     * @PaginaInfo(titolo="Consultazione bandi e manifestazioni d'interesse", sottoTitolo="mostra l'elenco dei bandi e delle manifestazioni d'interesse")
     * @Menuitem(menuAttivo="consultaBandi")
     */
    public function elencoBandiManifestazioniAction() {
        $datiRicerca = new RicercaBandoManifestazione();
        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        $procedure = $risultato["risultato"];
        $arrayTipi = [];
        foreach ($procedure as $p) {
            if ($p instanceof ManifestazioneInteresse) {
                $tipo = "Manifestazione d'interesse";
            } else {
                $tipo = "Bando";
            }
            $arrayTipi[$p->getId()] = $tipo;
        }
        return ['procedure' => $risultato["risultato"], "form_ricerca_procedure" => $risultato["form_ricerca"], "filtro_attivo" => $risultato["filtro_attivo"], 'tipi' => $arrayTipi];
    }

    /**
     * @Route("/elenco_bandi_pulisci", name="elenco_bandi_pulisci")
     */
    public function elencoBandiManifestazioniPulisciAction() {
        $this->get("ricerca")->pulisci(new RicercaBandoManifestazione());
        return $this->redirectToRoute("elenco_bandi");
    }
}
