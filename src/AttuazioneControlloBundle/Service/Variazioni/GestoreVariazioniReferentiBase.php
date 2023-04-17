<?php

namespace AttuazioneControlloBundle\Service\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneReferente;
use AttuazioneControlloBundle\Entity\VariazioneSingoloReferente;
use AttuazioneControlloBundle\Form\VariazioneSingoloReferenteType;
use BaseBundle\Exception\SfingeException;
use PaginaBundle\Services\Pagina;
use RichiesteBundle\Entity\Referente;
use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GestoreVariazioniReferentiBase extends GestoreVariazioniSpecifica implements IGestoreVariazioniReferenti {
    /** @var VariazioneReferente $variazione */
    protected $variazione;

    public function __construct(VariazioneReferente $variazione, IGestoreVariazioni $base, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->base = $base;
        $this->container = $container;
    }

    public function elencoReferenti(): Response {
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $this->variazione->getRichiesta()->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione", $this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Elenco Referenti");

        return $this->render('AttuazioneControlloBundle:Variazioni:elencoReferenti.html.twig', [
            'variazione' => $this->variazione,
        ]);
    }

    public function modificaReferente(Referente $referente): Response {
        $em = $this->getEm();
        /** @var VariazioneSingoloReferente|bool $variazioneReferente */
        $variazioneReferente = $this->variazione->getVariazioniSingoloReferente()->filter(function (VariazioneSingoloReferente $singolo) use ($referente) {
            return $singolo->getReferenza() == $referente;
        })->first();
        if (false === $variazioneReferente) {
            $variazioneReferente = new VariazioneSingoloReferente($this->variazione, $referente);
            $em->persist($variazioneReferente);
            $this->variazione->addVariazioniSingoloReferente($variazioneReferente);
        }

        $form = $this->createForm(VariazioneSingoloReferenteType::class, $variazioneReferente, [
            'disabled' => $this->variazione->isRichiestaDisabilitata(),
            'indietro' => $this->generateUrl('referenti_variazione', [
                'id_variazione' => $this->variazione->getId(),
            ]),
        ]);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getEm()->flush();
                return $this->addSuccesRedirect('Dati salvati correttamente', 'referenti_variazione', [
                    'id_variazione' => $this->variazione->getId(),
                ]);
            } catch (\Exception $e) {
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco progetti", $this->generateUrl("elenco_gestione_beneficiario"));
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_variazioni", ["id_richiesta" => $this->variazione->getRichiesta()->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Dettaglio variazione", $this->generateUrl("dettaglio_variazione", ["id_variazione" => $this->variazione->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Elenco referenti", $this->generateUrl("referenti_variazione", ["id_variazione" => $this->variazione->getId()]));
        $paginaService->aggiungiElementoBreadcrumb("Modifica referente");

        return $this->render('AttuazioneControlloBundle:Variazioni:cambioReferenti.html.twig', [
            'form' => $form->createView(),
            'variazione' => $this->variazione,
            'referente' => $referente,
        ]);
    }

    public function validaReferenti(): EsitoValidazione {
        if ($this->variazione->getVariazioniSingoloReferente()->isEmpty()) {
            return new EsitoValidazione(false, '', 'Compilare la sezione');
        }
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $violationList = $validator->validate($this->variazione->getVariazioniSingoloReferente());
        $res = new EsitoValidazione(true);
        /** @var ConstraintViolation $violation */
        foreach ($violationList as $violation) {
            $res->setEsito(false);
            $res->addMessaggio($violation->getMessage());
        }
        return $res;
    }

    public function controllaValiditaVariazione(): EsitoValidazione {
        $esitoParent = parent::controllaValiditaVariazione();
        $esitoreferenti = $this->validaReferenti();
        $esito = $esitoParent->merge($esitoreferenti);

        return $esito;
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

        return $this->render("AttuazioneControlloBundle:Variazioni:dettaglioVariazioneReferenti.html.twig", $dati);
    }

    protected function operazioniSpecificheInvioVariazione(): void {
    }

    protected function generaPdf(bool $facsimile = true): string {
        if ($this->variazione->isRichiestaDisabilitata()) {
            throw new SfingeException("Impossibile generare il pdf della richiesta nello stato in cui si trova");
        }

        $richiesta = $this->variazione->getRichiesta();
        $annualita = $this->container->get("gestore_piano_costo")->getGestore($richiesta->getProcedura())->getAnnualita($richiesta->getMandatario()->getId());
        $dati = [
            "variazione" => $this->variazione,
            "firmatario" => $this->variazione->getFirmatario(),
            "procedura" => $richiesta->getProcedura(),
            "capofila" => $richiesta->getMandatario()->getSoggetto(),
            "annualita" => $annualita,
            'facsimile' => $facsimile,
        ];
        $isFsc = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isFsc();
        $dati["is_fsc"] = $isFsc;
        /** @var PdfWrapper $pdfService */
        $pdfService = $this->container->get("pdf");
        $pdfService->load("@AttuazioneControllo/Pdf/Variazioni/referenti.html.twig", $dati);
        $pdf = $pdfService->binaryData();

        return $pdf;
    }

    public function eliminaSingolaVariazione(VariazioneSingoloReferente $singolo): Response {
        $this->variazione->removeVariazioniSingoloReferente($singolo);
        try {
            $this->getEm()->remove($singolo);
            $this->getEm()->flush();
            $this->addSuccess('Operazione effettuata con successo');
        } catch (\Exception $e) {
            $this->addError("Errore durante l'operazione");
        }

        return $this->redirectToRoute('referenti_variazione', [
            'id_variazione' => $this->variazione->getId(),
        ]);
    }
    
    public function validaDocumenti(): EsitoValidazione {
        $esito = new EsitoValidazione(true);
        $esito->setSezione("Documenti");
        
        
        return $esito;
    }
}
