<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use PaginaBundle\Services\Pagina;
use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancariProponente;
use AttuazioneControlloBundle\Form\VariazioneDatiBancariProponenteType;

class GestoreVariazioniDatiBancariBase extends GestoreVariazioniSpecifica implements IGestoreVariazioniDatiBancari {
    /**
     * @var VariazioneDatiBancari
     */
    protected $variazione;

    public function __construct(VariazioneDatiBancari $variazione, IGestoreVariazioni $base, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->base = $base;
        $this->container = $container;
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

        return $this->render("AttuazioneControlloBundle:Variazioni:dettaglioVariazioneDatiBancari.html.twig", $dati);
    }

    public function validaDatiBancari(): EsitoValidazione {
        $validazioneSingolaVariazione = \Closure::fromCallable([$this, 'validaDatiBancariProponente']);
        $esiti = $this->variazione->getDatiBancari()->map($validazioneSingolaVariazione)->toArray();
        $esito = \array_reduce($esiti, function (EsitoValidazione $carry, EsitoValidazione $esito): EsitoValidazione {
            return $esito->merge($carry);
        }, new EsitoValidazione(true));

        return $esito;
    }

    public function validaDatiBancariProponente(VariazioneDatiBancariProponente $datiBancari): EsitoValidazione {
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $errors = $validator->validate($datiBancari);
        $esito = new EsitoValidazione(true);
        if ($errors->count() > 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione('Sezione incompleta');
        }

        return $esito;
    }

    protected function generaPdf(bool $facsimile = true): string {
        if ($this->variazione->isRichiestaDisabilitata()) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }
        /** @var PdfWrapper $pdfService */
        $pdfService = $this->container->get("pdf");
        $twig = '@AttuazioneControllo/Pdf/Variazioni/variazione_dati_bancari.html.twig';
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

    public function controllaValiditaVariazione(): EsitoValidazione {
        $esitoParent = parent::controllaValiditaVariazione();
        $esitoDatiBancari = $this->validaDatiBancari();
        $esito = $esitoParent->merge($esitoDatiBancari);

        return $esito;
    }

    public function modificaDatiBancariProponente(VariazioneDatiBancariProponente $dati): Response {
        $variazione = $dati->getVariazione();
        $indietro = $this->generateUrl('dettaglio_variazione', [
            'id_variazione' => $variazione->getId(),
        ]);
        $form = $this->createForm(VariazioneDatiBancariProponenteType::class, $dati, [
            'disabled' => $variazione->isRichiestaDisabilitata(),
            'indietro' => $indietro,
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->flush();
                $this->addSuccess('Operazione effettuata con successo');

                return $this->redirect($indietro);
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError("Errore durante il salvataggio delle informazioni");
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('AttuazioneControlloBundle:Variazioni:dati_bancari_proponente.html.twig', $mv);
    }

    public function validaDocumenti(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Documenti");
        
        
        return $esito;
    }
    
    /**
     * Non sono richieste operazioni specifiche in fase di invio della variazione
     */
    protected function operazioniSpecificheInvioVariazione(): void{
    }
}
