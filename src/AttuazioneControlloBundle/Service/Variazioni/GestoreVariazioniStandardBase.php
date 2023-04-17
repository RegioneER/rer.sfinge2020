<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreVariazioniStandardBase extends GestoreVariazioniSpecifica {

    /** @var VariazioneRichiesta $variazione */
    protected $variazione;

    public function __construct(VariazioneRichiesta $variazione, IGestoreVariazioni $base, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->base = $base;
        $this->container = $container;
    }

    /**
     * Non sono richieste operazioni specifiche in fase di invio della variazione
     */
    protected function operazioniSpecificheInvioVariazione(): void {
        
    }

    protected function generaPdf(bool $facsimile = true): string {
        if ($this->variazione->isRichiestaDisabilitata()) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }
        /** @var PdfWrapper $pdfService */
        $pdfService = $this->container->get("pdf");
        $twig = '@AttuazioneControllo/Pdf/Variazioni/variazione_generica.html.twig';
        $dati = [
            'variazione' => $this->variazione,
            'facsimile' => $facsimile,
        ];
        //$dati["proroga"] = $proroga;
        $isFsc = $this->container->get("gestore_richieste")->getGestore($this->variazione->getRichiesta()->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        $pdfService->load($twig, $dati);
        $pdf = $pdfService->binaryData();

        return $pdf;
    }

    public function dettaglioVariazione(): Response {
        $richiesta = $this->variazione->getRichiesta();

        $dati = [
            "variazione" => $this->variazione,
            "avanzamenti" => $this->gestioneBarraAvanzamento(),
            'esito' => $this->controllaValiditaVariazione(),
        ];

        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $richiesta->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione");

        return $this->render("AttuazioneControlloBundle:Variazioni:dettaglioVariazione.html.twig", $dati);
    }

}
