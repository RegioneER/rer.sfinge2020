<?php

namespace AttuazioneControlloBundle\Controller\Controlli;

use AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto;
use AttuazioneControlloBundle\Entity\Controlli\ElementoChecklistControllo;
use AttuazioneControlloBundle\Form\Entity\ChecklistSpecifica;
use AttuazioneControlloBundle\Form\Entity\GestioneChecklistSpecifica;
use AttuazioneControlloBundle\Form\GestioneChecklistSpecificaType;
use BaseBundle\Annotation\ControlloAccesso;
use BaseBundle\Controller\BaseController;
use BaseBundle\Service\SpreadsheetFactory;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Entity\Richiesta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Route("/attuazione/controlli")
 */
class ControlliController extends BaseController {

    /**
     * @Route("/home_controlli/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"},  name="home_controlli")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco delle procedure con controlli")
     * @Menuitem(menuAttivo="elencoControlli")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Controlli")})
     */
    public function homeControlliAction() {
        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlliProcedura();

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);

        $em = $this->getEm();

        /*
         * Sono costretto a manipolare i risultati a posteriori per poter aggiungere
         * i risultati delle colonne richieste perchè con una singola query potrebbe essere molto ostico
         */
        foreach ($risultato["risultato"] as $campione) {
            $repository = $em->getRepository('AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura');
            $id_procedura = $campione->getProcedura()->getId();
            //Calcolo le varie colonne
            $rend_ammessa = $repository->getSpesaRendicontataAmmessa($id_procedura);
            $rend_ammessa_campione = $repository->getSpesaRendicontataAmmessaCampione($id_procedura);
            $imprese_campionate = $repository->getImpreseCampionate($id_procedura);
            $imprese_controllate = $repository->getImpreseControllate($id_procedura);
            //$spesa_controllata = $repository->getSpesaControllata($id_procedura);
            //$spesa_irregolare = $repository->getSpesaIrregolare($id_procedura);
            //$decertificazioni = $repository->getSpesaDecertificazioni($id_procedura);
            $revoca = $repository->getCampioniConRevoca($id_procedura);
            $cl_non_ammessa = $repository->getCampioniConClRendNonAmmessa($id_procedura);
            $cl_rend_ammessa = $repository->getCampioniConClAmmesseSenzaEsito($id_procedura);

            //Setto i valori di appoggio nel campione
            /* @var $campione \AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura */
            if (0 == $rend_ammessa) {
                $campione->setPercentualeCoperta('ND');
            } else {
                $campione->setPercentualeCoperta(100 * $rend_ammessa_campione / $rend_ammessa);
            }
            $campione->setImpreseCampionate($imprese_campionate);
            $campione->setImpreseControllate($imprese_controllate);
            //$campione->setSpesaControllata($spesa_controllata);
            //$campione->setSpesaIrregolare($spesa_irregolare);
            //$campione->setRettifiche($decertificazioni);
            $campione->setCampioneRevoche($revoca);
            $campione->setClNonAmmesse($cl_non_ammessa);
            $campione->setClRendAmmesse($cl_rend_ammessa);
        }

        $dati = [
            'menu_principale' => 'procedura',
            'risultati' => $risultato["risultato"],
            "formRicerca" => $risultato["form_ricerca"],
            "filtro_attivo" => $risultato["filtro_attivo"],];

        return $this->render('AttuazioneControlloBundle:Controlli:elencoControlliProcedure.html.twig', $dati);
    }

    /**
     * @Route("/elenco_controlli/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="elenco_controlli")
     * @PaginaInfo(titolo="Elenco controlli", sottoTitolo="mostra l'elenco dei controlli")
     * @Menuitem(menuAttivo="elencoControlli")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco controlli")})
     */
    public function elencoControlliAction(Request $request) {
        $datiRicerca = new \AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlli();
        $datiRicerca->setUtente($this->getUser());

        $risultato = $this->get("ricerca")->ricerca($datiRicerca);
        $formImportazione = $this->createFormBuilder()
                ->add('file', FileType::class, [
                    'label' => 'Foglio di calcolo importazione progetti',
                    'estensione' => 'xls, xlsx, ods, csv',
                    'constraints' => new Assert\File([
                        'mimeTypes' => [
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/vnd.oasis.opendocument.spreadsheet',
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'I formati supportati sono: OpenDocument spreadsheet document, Microsoft Excel (OpenXML), Microsoft Excel e CSV',
                            ]),
                ])
                ->add('importa', SubmitType::class)
                ->getForm();
        $formImportazione->handleRequest($request);
        if ($formImportazione->isSubmitted() && $formImportazione->isValid()) {
            if (!$this->isGranted("ROLE_SUPER_ADMIN") && !$this->isGranted("ROLE_SUPERVISORE_CONTROLLI")) {
                $this->addFlash('error', "Non sei abilitato ad eseguira l'operazione"); 
                return $this->redirectToRoute("home_controlli");
            }
            /** @var File */
            $file = $formImportazione->get('file')->getData();
            try {
                $progettiDaImportare = $this->effettuaParsingExcel($file);
                $em = $this->getEm();
                $controlliDaInserire = \array_map(function(Richiesta $richiesta): ControlloProgetto {
                    return new ControlloProgetto($richiesta);
                }, $progettiDaImportare);
                \array_walk($controlliDaInserire, function(ControlloProgetto $controllo) use($em){
                    $controllo->setTipologia('STANDARD');
                    $em->persist($controllo);
                });
                $em->flush();
                $numeroControlliInseriti = \count($controlliDaInserire);
                $this->addSuccess("Inseriti $numeroControlliInseriti controlli");
            } catch (\Exception $e) {
                $this->get('logger')->error('Errore durante l\'elaborazione del file');
            }
        }

        return $this->render('AttuazioneControlloBundle:Controlli:elencoControlli.html.twig', ['menu_principale' => 'campioni',
                    'risultati' => $risultato["risultato"],
                    "formRicerca" => $risultato["form_ricerca"],
                    "filtro_attivo" => $risultato["filtro_attivo"],
                    'formImportazione' => $formImportazione->createView(),
        ]);
    }

    private function effettuaParsingExcel(File $file): array {
        /** @var SpreadsheetFactory */
        $spreadSheetFactory = $this->get('phpoffice.spreadsheet');
        $spreadSheet = $spreadSheetFactory->readFile($file);
        $sheet = $spreadSheet->getActiveSheet();
        $valoriSheet = $sheet->rangeToArray("A2:A{$sheet->getHighestRow()}");
        $valoriSheetNormalizzati = \array_map('reset', $valoriSheet);
        $valoriSheetValidi = \array_filter($valoriSheetNormalizzati, 'is_numeric');

        //Filtro in funzione dei dati presenti su DB
        $dql = "SELECT r
            FROM RichiesteBundle:Richiesta r
            LEFT JOIN r.controlli cp WITH (cp.tipologia = 'STANDARD')
            WHERE r IN (:progetti) AND cp.id IS NULL";
        $progettiControlloNonPresente = $this->getEm()
                ->createQuery($dql)
                ->setParameter('progetti', $valoriSheetValidi)
                ->getResult();

        return $progettiControlloNonPresente;
    }

    /**
     * @Route("/elenco_controlli_pulisci", name="elenco_controlli_pulisci")
     */
    public function elencoControlliPulisciAction() {
        $this->get("ricerca")->pulisci(new \AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlli());
        return $this->redirectToRoute("elenco_controlli");
    }

    /**
     * @Route("/elenco_controlli_procedure_pulisci", name="elenco_controlli_procedure_pulisci")
     */
    public function elencoControlliProcedurePulisciAction() {
        $this->get("ricerca")->pulisci(new \AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlliProcedura());
        return $this->redirectToRoute("home_controlli");
    }

    /**
     * @Route("/valuta/{id_valutazione_checklist}", name="valuta_checklist_controlli")
     * @Menuitem(menuAttivo="elencoControlli")
     * ControlloAccesso(contesto="procedura", classe="IstruttorieBundle:ValutazioneChecklistIstruttoria", opzioni={"id" = "id_valutazione_checklist"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_valutazione_checklist
     */
    public function valutaChecklistAction($id_valutazione_checklist) {
        $valutazione_checklist = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ValutazioneChecklistControllo")->find($id_valutazione_checklist);
        return $this->get("gestore_controlli")->getGestore($valutazione_checklist->getControlloProgetto()->getProcedura())->valutaChecklist($valutazione_checklist);
    }

    /**
     * @Route("/{id_controllo}/riepilogo", name="riepilogo_controllo")
     * @PaginaInfo(titolo="Riepilogo del controllo", sottoTitolo="dati riepilogativi del controllo")
     * @Menuitem(menuAttivo="elencoControlli")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function riepilogoControlloAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->riepilogoControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/esito_finale", name="esito_finale_controlli")
     * @PaginaInfo(titolo="Esito finale istruttoria pagamento")
     * @Menuitem(menuAttivo="elencoControlli")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function esitoFinaleAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->esitoFinale($controllo);
    }

    /**
     * @Route("/{id_controllo}/documenti_controllo", name="documenti_controlli")
     * @PaginaInfo(titolo="Documenti controllo progetto", sottoTitolo="documenti caricati per il controllo del progetto")
     * @Menuitem(menuAttivo="elencoControlli")
     * @param mixed $id_controllo
     */
    public function documentiControlloAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->documentiControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/documenti_controllo_procedura", name="documenti_controllo_procedura")
     * @PaginaInfo(titolo="Documenti controllo procedura", sottoTitolo="documenti caricati per il controllo della procedura")
     * @Menuitem(menuAttivo="elencoControlli")
     * @param mixed $id_controllo
     */
    public function documentiControlloProceduraAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->documentiControlloProcedura($controllo);
    }

    /**
     * @Route("/{id_controllo}/elimina_documento_controllo/{id_documento}/{verbale}", defaults={"verbale" : "0"}, name="elimina_documento_controllo")
     * @param mixed $id_controllo
     * @param mixed $id_documento
     * @param mixed $verbale
     */
    public function eliminaDocumentoControlloAction($id_controllo, $id_documento, $verbale) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        $documento_controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\DocumentoControllo")->find($id_documento);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->eliminaDocumentoControllo($controllo, $documento_controllo, $verbale);
    }

    /**
     * @Route("/{id_controllo}/elimina_documento_controllo_procedura/{id_documento}", name="elimina_documento_controllo_procedura")
     * @param mixed $id_controllo
     * @param mixed $id_documento
     */
    public function eliminaDocumentoControlloProceduraAction($id_controllo, $id_documento) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProcedura")->find($id_controllo);
        $documento_controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\DocumentoControlloProcedura")->find($id_documento);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->eliminaDocumentoControlloProcedura($controllo, $documento_controllo);
    }

    /**
     * @Route("/{id_controllo}/valuta_sopralluogo_form", name="valuta_sopralluogo_form")
     * @PaginaInfo(titolo="Esito finale istruttoria pagamento")
     * @Menuitem(menuAttivo="elencoControlli")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function valutaSopralluogoFormAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->valutaSopralluogoForm($controllo);
    }

    /**
     * @Route("/estrazioni", name="estrazioni")
     * @PaginaInfo(titolo="Elenco estrazioni disponibili")
     * @Menuitem(menuAttivo="elencoControlli")
     */
    public function estrazioniAction() {
        $dati = ['menu_principale' => 'estrazioni'];
        return $this->render('AttuazioneControlloBundle:Controlli:estrazioni.html.twig', $dati);
    }

    /**
     * @Route("/estrazioni/estrazione_campioni", name="estrazione_campioni")
     */
    public function estrazioniCampioniInLoco() {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        return $this->get("gestore_controlli")->getGestore($procedura)->estraiCampioniLoco();
    }

    /**
     * @Route("/estrazioni/estrazione_campioni_pagamenti", name="estrazione_campioni_pagamenti")
     */
    public function estrazioniPagamentiCampioniInLoco() {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        return $this->get("gestore_controlli")->getGestore($procedura)->estraiPagamentiCampioniLoco();
    }

    /**
     * @Route("/estrazioni/estrazione_campioni_progetti", name="estrazione_campioni_progetti")
     */
    public function estrazioniProgettiCampioniInLoco() {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        return $this->get("gestore_controlli")->getGestore($procedura)->estraiRichiesteCampioniLoco();
    }

    /**
     * @Route("/estrazioni/estrazione_giustificativi_progetto/{id_richiesta}", name="estrazione_giustificativi_progetto")
     * @param mixed $id_richiesta
     */
    public function estrazioniGiustificativiRichiesta($id_richiesta) {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        return $this->get("gestore_controlli")->getGestore($procedura)->estrazioneGiustificativiProgetto($id_richiesta);
    }

    /**
     * @Route("/estrazioni/estrazione_universo_progetti/", name="estrazione_universo_progetti")
     */
    public function estrazioniUniversoProgetti() {
        $em = $this->getEm();
        $procedura = $em->getRepository("SfingeBundle\Entity\Procedura")->find(4);
        return $this->get("gestore_controlli")->getGestore($procedura)->estraiProgettiUniverso();
    }

    /**
     * @Route("/gestione_checklist_specifiche/", name="gestione_checklist_specifiche")
     * @PaginaInfo(titolo="Modifica controlli specifici", sottoTitolo="Permette la modifica delle domande specifiche per procedura")
     * @Menuitem(menuAttivo="gestione-checklist-specifiche")
     */
    public function gestioneChecklistSpecificheAction(Request $request): Response {
        $elementiChecklist = $this->getEm()->getRepository(ElementoChecklistControllo::class)->getElementiSpecificoStandard();
        if (!$this->isGranted("ROLE_SUPERVISORE_CONTROLLI") || $this->isGranted("ROLE_ISTRUTTORE_INVITALIA")) {
            $this->addFlash("error", "Operazione non ammessa");
            return $this->redirectToRoute("home");            
        }

        $query = $this->getEm()->createQuery("
			select p from SfingeBundle:Procedura p
			where p.id in (:ids)
		");
        /** @var ChecklistSpecifica[] $elementiForm */
        $elementiForm = array_map(function (ElementoChecklistControllo $elemento) use ($query) {
            $procedure = $query->setParameter('ids', $elemento->getProcedure())->getResult();
            return new ChecklistSpecifica($elemento, $procedure);
        }, $elementiChecklist);
        $elementoRootForm = new GestioneChecklistSpecifica($elementiForm);

        $form = $this->createForm(GestioneChecklistSpecificaType::class, $elementoRootForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($elementiForm as $elemento) {
                $ids = \array_map(function (Procedura $p) {
                    return $p->getId();
                }, $elemento->procedure);

                $elemento->elemento->setProcedure($ids);
            }

            try {
                $this->getEm()->flush();

                $this->addSuccess('Operazione eseguita con successo');
            } catch (\Exception $e) {
                $this->addError('Errore durante il salvataggio delle informazioni');
                $this->get('logger')->error($e->getTraceAsString());

                throw $e;
            }
        }
        $mv = [
            'form' => $form->createView(),
        ];

        return $this->render('AttuazioneControlloBundle:Controlli:gestioneChecklistSpecifica.html.twig', $mv);
    }

    /**
     * @Route("/{id_controllo}/verbale_desk_form", name="verbale_desk_form")
     * @PaginaInfo(titolo="Verbale del controllo fase desk", sottoTitolo="dati riepilogativi del controllo fase desk")
     * @Menuitem(menuAttivo="elencoControlli")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function verbaleDeskControlloAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->verbaleDeskControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/verbale_sopralluogo_form", name="verbale_sopralluogo_form")
     * @PaginaInfo(titolo="Verbale del controllo fase sopralluogo", sottoTitolo="dati riepilogativi del controllo fase sopralluogo")
     * @Menuitem(menuAttivo="elencoControlli")
     * ControlloAccesso(contesto="procedura", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"}, azione=\SfingeBundle\Security\ProceduraVoter::READ)
     * @param mixed $id_controllo
     */
    public function verbaleSopralluogoControlloAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        return $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->verbaleSopralluogoControllo($controllo);
    }

    /**
     * @Route("/{id_controllo}/genera_verbale_desk", name="genera_verbale_desk")
     * @param mixed $id_controllo
     */
    public function generaVerbaleDeskControlloAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        $response = $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->generaVerbaleDeskControllo($controllo);
        return $response;
    }

    /**
     * @Route("/{id_controllo}/genera_verbale_sopralluogo", name="genera_verbale_sopralluogo")
     * @param mixed $id_controllo
     */
    public function generaVerbaleSopralluogoControlloAction($id_controllo) {
        $controllo = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto")->find($id_controllo);
        $response = $this->get("gestore_controlli")->getGestore($controllo->getProcedura())->generaVerbaleSopralluogoControllo($controllo);
        return $response;
    }

}
