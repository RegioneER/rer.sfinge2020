<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\DocumentoFile;
use AttuazioneControlloBundle\Entity\DocumentoVariazione;
use BaseBundle\Service\BaseServiceTrait;
use Symfony\Component\Form\FormInterface;

abstract class AGestoreVariazioni implements IGestoreVariazioni {
    use BaseServiceTrait;

    /**
     * @var VariazioneRichiesta
     */
    protected $variazione;

    public function documentiVariazione(): Response {
        $em = $this->getEm();

        $documenti = $em->getRepository(DocumentoVariazione::class)->findByVariazione($this->variazione);
        $documenti_variazione = [];
        $documenti_istruttoria = [];

        foreach ($documenti as $documento) {
            if ('ISTRUTTORIA_VARIAZIONE' == $documento->getDocumentoFile()->getTipologiaDocumento()->getCodice()) {
                $documenti_istruttoria[] = $documento;
            } else {
                $documenti_variazione[] = $documento;
            }
        }

        $listaTipi = $this->getEm()->getRepository(TipologiaDocumento::class)->ricercaDocumentiIstruttoriaVariazione();
        $documento_file = new DocumentoFile();
        $documento_istruttoria = new DocumentoVariazione();
        $opzioni_form["lista_tipi"] = $listaTipi;

        $form = $this->createForm(\DocumentoBundle\Form\Type\DocumentoFileType::class, $documento_file, $opzioni_form);
        $form->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, ['label' => 'Salva']);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get("documenti")->carica($documento_file, 0);

                $documento_istruttoria->setDocumentoFile($documento_file);
                $documento_istruttoria->setVariazione($this->variazione);
                $em->persist($documento_istruttoria);

                $em->flush();
                return $this->redirect($this->generateUrl('documenti_istruttoria_variazione', ['id_variazione' => $this->variazione->getId()]));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_istruttoria_variazioni"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Documenti variazione");

        $dati = [
            "variazione" => $this->variazione,
            "menu" => "documenti",
            "documenti_istruttoria" => $documenti_istruttoria,
            "documenti_variazione" => $documenti_variazione,
            "form" => $form->createView(),
        ];

        return $this->render("AttuazioneControlloBundle:Istruttoria/Variazioni:documentiVariazione.html.twig", $dati);
    }

    protected function aggiornaFinanziamentoProgetto(): void {
        $richiesta = $this->variazione->getRichiesta();
        /** @var \MonitoraggioBundle\Service\GestoreFinanziamentoService $service */
        $service = $this->container->get('monitoraggio.gestore_finanziamento');

        $gestore = $service->getGestore($richiesta);

        $gestore->aggiornaFinanziamento(true);
        $gestore->persistFinanziamenti();
    }

    public function eliminaDocumentoIstruttoriaVariazione(DocumentoVariazione $documento_variazione): Response {
        $em = $this->getEm();
        $this->variazione = $documento_variazione->getVariazione();

        try {
            $this->container->get("documenti")->cancella($documento_variazione->getDocumentoFile(), 0);
            $em->remove($documento_variazione);
            $em->flush();
            return $this->addSuccesRedirect("Il documento è stato correttamente eliminato", 'documenti_istruttoria_variazione', ['id_variazione' => $this->variazione->getid()]);
        } catch (\Exception $e) {
            return $this->addErrorRedirect("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.", 'documenti_istruttoria_variazione', ['id_variazione' => $this->variazione->getid()]);
        }
    }

    public function riepilogoVariazione(): Response {
        /** @var Pagina $paginaService */
        $paginaService = $this->container->get("pagina");
        $paginaService->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_istruttoria_variazioni"));
        $paginaService->aggiungiElementoBreadcrumb("Riepilogo variazione");

        $dati = ["variazione" => $this->variazione, "menu" => "riepilogo"];

        return $this->render("AttuazioneControlloBundle:Istruttoria/Variazioni:riepilogoVariazione.html.twig", $dati);
    }

    public function esitoFinale(): Response {
        $options = [
            "url_indietro" => $this->generateUrl('esito_finale_istruttoria_variazioni', ["id_variazione" => $this->variazione->getId()]),
            "disabled" => !$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE_ATC"),
            "disabled_campi" => !is_null($this->variazione->getEsitoIstruttoria()),
            "disabled_invalida" => !is_null($this->variazione->getEsitoIstruttoria()),
            "disabled_valida" => is_null($this->variazione->getEsitoIstruttoria()),
            'validation_groups' => ['Default', 'validazione_variazione'],
        ];

        $form = $this->createForm(\AttuazioneControlloBundle\Form\Istruttoria\EsitoVariazioneType::class, $this->variazione, $options);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $this->validaEsito($form);

            if ($form->isValid()) {
                $em = $this->getEm();
                $connection = $em->getConnection();
                try {
                    $connection->beginTransaction();
                    $pulsanti = $form->get("pulsanti");
                    if ($pulsanti->has("pulsante_invalida") && $pulsanti->get("pulsante_invalida")->isClicked()) {
                        $this->variazione->setEsitoIstruttoria(null);
                        $em->flush();
                        $connection->commit();
                        $this->addFlash('success', "Istruttoria variazione invalidata correttamente");
                        return $this->redirect($this->generateUrl('esito_finale_istruttoria_variazioni', ['id_variazione' => $this->variazione->getId()]));
                    }
                    $this->applicaVariazione();
                    $this->variazione->setDataValidazione(new \DateTime());
                    $em->flush();
                    $connection->commit();
                    $this->addFlash('success', "Esito finale istruttoria variazione salvato correttamente");

                    return $this->redirect($this->generateUrl('elenco_istruttoria_variazioni'));
                } catch (\Exception $e) {
                    if ($connection->isTransactionActive()) {
                        $connection->rollBack();
                    }
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco variazioni", $this->generateUrl("elenco_istruttoria_variazioni"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Esito finale istruttoria variazione");

        $twig = "AttuazioneControlloBundle:Istruttoria\Variazioni:esitoFinale.html.twig";
        $dati = [
            "variazione" => $this->variazione,
            "menu" => "esito",
            "form" => $form->createView(),
        ];

        return $this->render($twig, $dati);
    }

    protected function validaEsito(FormInterface &$form): void {
    }

    abstract protected function applicaVariazione(): void;
}
