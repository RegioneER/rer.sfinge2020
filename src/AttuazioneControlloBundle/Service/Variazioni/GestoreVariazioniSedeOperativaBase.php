<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use AttuazioneControlloBundle\Form\CambioSedeType;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\SedeOperativa;
use RichiesteBundle\Utility\EsitoValidazione;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Entity\SedeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreVariazioniSedeOperativaBase extends GestoreVariazioniSpecifica implements IGestoreVariazioniSedeOperativa {

    /** @var VariazioneSedeOperativa $variazione */
    protected $variazione;

    public function __construct(VariazioneSedeOperativa $variazione, IGestoreVariazioni $base, ContainerInterface $container) {
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
        $twig = '@AttuazioneControllo/Pdf/Variazioni/variazione_sede_operativa.html.twig';
        $dati = [
            'variazione' => $this->variazione,
            'facsimile' => $facsimile,
        ];
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

        return $this->render("AttuazioneControlloBundle:Variazioni:dettaglioVariazioneSedeOperativa.html.twig", $dati);
    }

    public function cambioSedeOperativa(): Response {
        //$this->aggiungiSedeOperativaSeNonPresente();
        $indietro = $this->generateUrl(
                'dettaglio_variazione',
                ['id_variazione' => $this->variazione->getId()]
        );
        $form = $this->createForm(CambioSedeType::class, $this->variazione, [
            'indietro' => $indietro,
            'disabled' => $this->variazione->isRichiestaDisabilitata(),
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->flush();
                return $this->addSuccesRedirect(
                                'Operazione effettuata con successo',
                                'dettaglio_variazione',
                                ['id_variazione' => $this->variazione->getId()]
                );
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        /** @var SedeRepository $sedeRepo */
        $sedeRepo = $this->getEm()->getRepository(Sede::class);
        $sediNonAssociate = $sedeRepo->getNuoveSediNonAssociate($this->variazione->getRichiesta())->getQuery()->getResult();
        $dati = [
            'form' => $form->createView(),
            'variazione' => $this->variazione,
            'sediAssociabili' => \count($sediNonAssociate) > 0,
        ];
        return $this->render('AttuazioneControlloBundle:Variazioni:cambioSedeOperativa.html.twig', $dati);
    }

    private function aggiungiSedeOperativaSeNonPresente(): void {
        $mandatario = $this->variazione->getRichiesta()->getMandatario();
        $isSedePresente = $mandatario->getSedi()->count() > 0;
        if ($isSedePresente) {
            return;
        }

        // Se la sede non è presente si provvede ad inserila
        $sedeLegale = $mandatario->getSoggetto()->getSede();
        $this->getEm()->persist($sedeLegale);

        $sedeOperativa = new SedeOperativa($mandatario);
        $sedeOperativa->setSede($sedeLegale);
        $mandatario->addSedi($sedeOperativa);

        $this->getEm()->persist($sedeOperativa);
        $this->getEm()->flush();
    }

    public function validaSedeOperativa(): EsitoValidazione {
        if ($this->variazione->getSedeOperativaVariata() && $this->variazione->getAutodichiarazione()) {
            return new EsitoValidazione(true);
        }

        return new EsitoValidazione(false, null, 'Sezione incompleta');
    }
    
    public function validaDocumenti(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Documenti");
        
        
        return $esito;
    }

}
