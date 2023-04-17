<?php

namespace SoggettoBundle\Controller;

use DocumentoBundle\Entity\TipologiaDocumento;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Form\Entity\DocumentiIncarico;
use SoggettoBundle\Form\Entity\RicercaPersonaIncaricabile;
use SoggettoBundle\Form\RicercaPersonaIncaricabileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\PaginaInfo;
use Symfony\Component\HttpFoundation\Request;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use SoggettoBundle\Entity\IncaricoPersonaRichiesta;

/**
 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco incarichi", route="elenco_incarichi")})
 */
class IncaricoGestioneController extends SoggettoBaseController {

    /**
     * @Route("/revoca_incarico/{id_incarico}/{soggetto_id}", defaults={"soggetto_id" : false}, name="revoca_incarico")
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "id_incarico"})
     * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "id_incarico"})
     * @param mixed $id_incarico
     * @param mixed $soggetto_id
     */
    public function revocaIncaricoAction($id_incarico, $soggetto_id) {
        $this->get('base')->checkCsrf('token');
        if (false != $soggetto_id && $this->isSuperAdmin()) {
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggetto_id);
            $incaricoPersona = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->find($id_incarico);
        } else {
            $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());

            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }

            if (!$this->isUtentePrincipale()) {
                return $this->addErrorRedirect("Solo l'utente principale può revocare un incarico", "elenco_incarichi");
            }

            $incaricoPersona = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->find($id_incarico);
            if (is_null($incaricoPersona)) {
                return $this->addErrorRedirect("Incarico non trovato", "elenco_incarichi");
            }

            //se è il mio incarico, eccetto il LR o il DELEGATO, non lo posso revocare
            if ($incaricoPersona->getIncaricato()->getId() == $this->getPersonaId() && "LR" != $incaricoPersona->getTipoIncarico() && "DELEGATO" != $incaricoPersona->getTipoIncarico()) {
                return $this->addErrorRedirect("Non è possibile revocare il proprio incarico", "elenco_incarichi");
            }

            //se il soggetto ha un solo incarico non lo posso revocare
            $numeroIncarichiAttivi = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->contaAttiviDaSoggetto($soggetto);
            if (1 == $numeroIncarichiAttivi) {
                return $this->addErrorRedirect("Non è possibile revocare l'unico incarico attivo", "elenco_incarichi");
            }

            //non posso revocare l'unico amministratore
            if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::UTENTE_PRINCIPALE)) {
                $numeroAmministratoreAttivi = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->contaAttiviDaSoggettoIncarico($soggetto, $incaricoPersona->getTipoIncarico());
                if (1 == $numeroAmministratoreAttivi) {
                    return $this->addErrorRedirect("Non è possibile revocare l'unico amministratore attivo", "elenco_incarichi");
                }
            }
        }

        $incaricoPersona->setStato($this->trovaDaCostante(new StatoIncarico(), StatoIncarico::REVOCATO));
        try {
            $this->getEm()->persist($incaricoPersona);
            $this->getEm()->flush();
            $this->addFlash("success", "Operazione eseguita");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute("elenco_incarichi");
    }

    /**
     * @Route("/cancella_incarico/{id_incarico}", name="cancella_incarico")
     * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "id_incarico"})
     * @param mixed $id_incarico
     */
    public function cancellaIncaricoAction($id_incarico) {
        if (!$this->isSuperAdmin()) {
            return $this->addErrorRedirect("Non hai i privilegi per effettuare questa operazione", "elenco_incarichi");
        }
        $this->get('base')->checkCsrf('token');
        $incaricoPersona = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->find($id_incarico);
        try {
            $this->getEm()->remove($incaricoPersona);
            $this->getEm()->flush();
            $this->addFlash("success", "Operazione eseguita");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute("elenco_incarichi");
    }

    /**
     * @Route("/riattiva_incarico/{id_incarico}/{soggetto_id}", defaults={"soggetto_id" : false}, name="riattiva_incarico")
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "id_incarico"})
     * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "id_incarico"})
     * @param mixed $id_incarico
     * @param mixed $soggetto_id
     */
    public function riattivaIncaricoAction($id_incarico, $soggetto_id) {
        $this->get('base')->checkCsrf('token');
        if (false != $soggetto_id && $this->isSuperAdmin()) {
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggetto_id);
            $incaricoPersona = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->find($id_incarico);
        } else {
            $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }

            if (!$this->isUtentePrincipale()) {
                return $this->addErrorRedirect("Solo l'utente principale può attivare un incarico", "elenco_incarichi");
            }

            $incaricoPersona = $this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->find($id_incarico);
            if (is_null($incaricoPersona)) {
                return $this->addErrorRedirect("Incarico non trovato", "elenco_incarichi");
            }

            if (!$incaricoPersona->getStato()->uguale(StatoIncarico::REVOCATO) && !$incaricoPersona->getStato()->uguale(StatoIncarico::BOCCIATO)) {
                return $this->addErrorRedirect("Incarico non attivabile", "elenco_incarichi");
            }
        }
        $incaricoPersona->setStato($this->trovaDaCostante(new StatoIncarico(), StatoIncarico::ATTIVO));
        try {
            $this->getEm()->persist($incaricoPersona);
            $this->getEm()->flush();
            $this->addFlash("success", "Operazione eseguita");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute("elenco_incarichi");
    }

    /**
     * @Template("SoggettoBundle:Incarico:selezioneIncarico.html.twig")
     * @Route("/selezione_incarico/{soggetto_id}", defaults={"soggetto_id" : false}, name="selezione_incarico")
     * @PaginaInfo(titolo="Selezione incarico", sottoTitolo="pagina per la selezione dell'incarico")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Selezione incarico")})
     * @Menuitem(menuAttivo="selezioneSoggetti")
     *
     * @param mixed $soggetto_id
     */
    public function selezioneIncaricoAction(Request $request, $soggetto_id) {
        if (false != $soggetto_id && !$this->isSuperAdmin()) {
            return $this->addErrorRedirect("Non hai i privilegi per effettuare questa operazione", "elenco_incarichi");
        }

        if (false != $soggetto_id && $this->isSuperAdmin()) {
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggetto_id);
        } else {
            $soggettoSession = $request->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }

            if (!$this->isUtentePrincipale()) {
                return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico", "elenco_incarichi");
            }
        }
        $incaricoPersona = new IncaricoPersona();

        $form = $this->createForm('SoggettoBundle\Form\IncaricoType', $incaricoPersona, ["url_indietro" => $this->generateUrl("elenco_incarichi"), "admin" => $this->isSuperAdmin()]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                //OGGI 25/11/2019, Appurato che è troppo limitante il blocco, permettiamo la possibilità di più LR
                //evito che ci siano due legali rappresentanti
                /* if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::LR)) {
                  if ($this->getEm()->getRepository("SoggettoBundle:Soggetto")->soggettoHaIncaricoAttivoAttesa($soggetto->getId(), $incaricoPersona->getTipoIncarico()->getCodice())) {
                  return $this->addWarningRedirect("Il soggetto selezionato risulta avere un legale rappresentante attivo o in attivazione", "elenco_incarichi");
                  }
                  } */
                if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::DELEGATO)) {
                    $incaricoLr = $this->trovaDaCostante(new TipoIncarico(), TipoIncarico::LR);
                    if (!$this->getEm()->getRepository("SoggettoBundle:IncaricoPersona")->haIncaricoAttivo($soggetto, $incaricoLr)) {
                        return $this->addWarningRedirect("Un delegato può essere associato solo dopo aver indicato e attivato un legale rappresentante", "elenco_incarichi");
                    }
                }
                $this->getSession()->set(self::SESSIONE_INCARICO, $incaricoPersona);
                if (false != $soggetto_id && $this->isSuperAdmin()) {
                    return $this->redirectToRoute("selezione_incaricato", ["soggetto_id" => $soggetto_id]);
                } else {
                    return $this->redirectToRoute("selezione_incaricato");
                }
            }
        }
        return ["soggetto" => $soggetto, "form" => $form->createView()];
    }

    /**
     * @Template("SoggettoBundle:Incarico:selezioneIncaricato.html.twig")
     * @Route("/selezione_incaricato/{soggetto_id}", defaults={"soggetto_id" : false}, name="selezione_incaricato")
     * @PaginaInfo(titolo="Selezione incaricato", sottoTitolo="pagina per la selezione della persona da incaricare")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Selezione incarico", route="selezione_incarico", parametri={"soggetto_id"}), @ElementoBreadcrumb(testo="Selezione incaricato")})
     * @Menuitem(menuAttivo="selezioneSoggetti")
     * @param mixed $soggetto_id
     */
    public function selezionePersonaIncaricataAction(Request $request, $soggetto_id) {
        if (false != $soggetto_id && !$this->isSuperAdmin()) {
            return $this->addErrorRedirect("Non hai i privilegi per effettuare questa operazione", "elenco_incarichi");
        }

        if (false != $soggetto_id && $this->isSuperAdmin()) {
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggetto_id);
        } else {
            $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }

            if (!$this->isUtentePrincipale()) {
                return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico", "elenco_incarichi");
            }
        }
        $incaricoPersona = $this->getSession()->get(self::SESSIONE_INCARICO);
        if (is_null($incaricoPersona)) {
            return $this->addErrorRedirect("Non è stato trovato nessun incarico da modificare", "elenco_incarichi");
        }

        $ricercaPersonaIncaricabile = new RicercaPersonaIncaricabile();
        $ricercaPersonaIncaricabile->setTipoIncarico($incaricoPersona->getTipoIncarico());
        $ricercaPersonaIncaricabile->setSoggettoId($soggetto->getId());
        $form = $this->createForm(new RicercaPersonaIncaricabileType(), $ricercaPersonaIncaricabile);
        $incaricabili = [];
        $aiuto = null;
        $nuovaPersona = false;
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                //Cerco se è un delegato ed è già presente
                if ($incaricoPersona->getTipoIncarico() == 'DELEGATO') {
                    $giaDelegato = $this->getEm()->getRepository("AnagraficheBundle:Persona")->cercaDelegatoPresente($ricercaPersonaIncaricabile);
                    if (count($giaDelegato) > 0) {
                        $aiuto = 'L\'utente cercato risulta essere già associato come DELEGATO per il soggetto giuridico; è possibile aggiornare la documentazione di delega nella sezione "INCARICHI" ';
                        return ["form" => $form->createView(), "incaricabili" => $incaricabili, "aiuto" => $aiuto, "nuova_persona" => $nuovaPersona, "soggetto_id" => $soggetto->getId()];
                    }
                }
                //cerco le possibili persone e mostro la lista
                $incaricabili = $this->getEm()->getRepository("AnagraficheBundle:Persona")->cercaIncaricabili($ricercaPersonaIncaricabile);
                if (0 == count($incaricabili)) {
                    if ($incaricoPersona->getTipoIncarico()->hasRuoloApplicativo()) {
                        $aiuto = "Per l'incarico selezionato occorre indicare un utente del sistema, si invita a far registrare la persona indicata";
                    } else {
                        $aiuto = "La persona cercata non è stata trovata nel sistema. E' possibile inserire una nuova persona ";
                        $nuovaPersona = true;
                    }
                }
            }
        }
        return ["form" => $form->createView(), "incaricabili" => $incaricabili, "aiuto" => $aiuto, "nuova_persona" => $nuovaPersona, "soggetto_id" => $soggetto->getId()];
    }

    /**
     * @Route("/associa_incaricato/{persona_id}/{persona_inserita}", defaults={"persona_inserita" : false},  name="associa_incaricato")
     * @param mixed $persona_id
     * @param mixed $persona_inserita
     */
    public function associaPersonaIncaricataAction($persona_id, $persona_inserita, Request $request) {
        $this->get('base')->checkCsrf('token');
        if ($request->query->has("soggetto_id") && $this->isSuperAdmin()) {
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($request->query->get("soggetto_id"));
        } else {
            $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
            $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
            if (is_null($soggetto)) {
                return $this->addErrorRedirect("Soggetto non specificato", "home");
            }

            if (!$this->isUtentePrincipale()) {
                return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico", "elenco_incarichi");
            }
        }
        $incaricoPersona = $this->getSession()->get(self::SESSIONE_INCARICO);
        if (is_null($incaricoPersona)) {
            return $this->addErrorRedirect("Non è stato trovato nessun incarico da modificare", "elenco_incarichi");
        }

        //a seconda dell'incarico mostro gli ulteriori requisiti
        $persona = $this->getEm()->getRepository("AnagraficheBundle:Persona")->find($persona_id);
        if (is_null($persona)) {
            return $this->addErrorRedirect("Persona non trovata", "elenco_incarichi");
        }

        //TODO controllare che la persono selezionara non sia gia delegato o legale rappresentate
        //in modo che non abbia i due incarichi contemporaneamente
        //Controllo se è già delegato
        if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::LR)) {
            $esito = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->personaHaDelegatoOLegaleAttivoPerSoggetto($persona->getId(), $soggetto->getId(), TipoIncarico::DELEGATO);
            if ($esito) {
                return $this->addErrorRedirect("La persona selezionata è già delegato per il soggetto in gestione ", "elenco_incarichi");
            }
        }

        if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::CONSULENTE) ||
            $incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::OPERATORE) ||
            $incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::OPERATORE_RICHIESTA) ||
            $incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::UTENTE_PRINCIPALE) ||
            $incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::LR)) {
            //riattacco gli oggetti della sessione
            $soggetto = $this->getEm()->merge($soggetto);
            $tipoIncarico = $this->getEm()->merge($incaricoPersona->getTipoIncarico());

            $_incaricoPersona = new IncaricoPersona();
            $_incaricoPersona->setTipoIncarico($tipoIncarico);
            $_incaricoPersona->setSoggetto($soggetto);
            $_incaricoPersona->setIncaricato($persona);
            $_incaricoPersona->setStato($this->trovaDaCostante(new StatoIncarico(), StatoIncarico::ATTIVO));
            try {
                $this->getEm()->persist($_incaricoPersona);
                $this->getEm()->flush();
                $this->addFlash("success", "Incarico correttamente aggiunto");
            } catch (\Exception $e) {
                $this->getEm()->rollback();
                $this->addFlash('error', $e->getMessage());
            }
            //se serve per tutti gli incarichi si toglie il commento
            /* if (!$this->invioEmailRichiestaIncarico($_incaricoPersona, $this->getUser())) {
              $this->addFlash("warning", "Richiesta di incarico creata correttamente, ma non è stato possibile inviare la email");
              } */

            return $this->redirectToRoute("elenco_incarichi");
        } else {
            return $this->redirectToRoute("documenti_incarico", ["persona_id" => $persona_id, "persona_inserita" => $persona_inserita]);
        }
    }

    /**
     * @Template("SoggettoBundle:Incarico:caricaDocumentiIncarico.html.twig")
     * @Route("/documenti_incarico/{persona_id}/{persona_inserita}", defaults={"persona_inserita" : false}, name="documenti_incarico")
     * @PaginaInfo(titolo="Documenti incarico", sottoTitolo="pagina per il caricamento dei documenti di incarico")
     * @Breadcrumb(elementi={
     * 	@ElementoBreadcrumb(testo="Selezione incarico", route="selezione_incarico"),
     * 	@ElementoBreadcrumb(testo="Selezione incaricato", route="selezione_incaricato"),
     * 	@ElementoBreadcrumb(testo="Documenti incarico")
     * 	})
     * @Menuitem(menuAttivo="selezioneSoggetti")
     * @param mixed $persona_id
     * @param mixed $persona_inserita
     */
    public function caricaDocumentiIncaricoAction(Request $request, $persona_id, $persona_inserita) {

        $soggettoSession = $this->getSession()->get(self::SESSIONE_SOGGETTO);
        $soggetto = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->findOneById($soggettoSession->getId());
        if (is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non specificato", "home");
        }

        if (!$this->isUtentePrincipale()) {
            return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico", "elenco_incarichi");
        }

        $incaricoPersona = $this->getSession()->get(self::SESSIONE_INCARICO);
        if (is_null($incaricoPersona)) {
            return $this->addErrorRedirect("Non è stato trovato nessun incarico da modificare", "elenco_incarichi");
        }

        //a seconda dell'incarico mostro gli ulteriori requisiti
        $persona = $this->getEm()->getRepository("AnagraficheBundle:Persona")->find($persona_id);
        if (is_null($persona)) {
            return $this->addErrorRedirect("Persona non trovata", "elenco_incarichi");
        }

        if ($incaricoPersona->getTipoIncarico()->hasRuoloApplicativo()) {
            return $this->addErrorRedirect("Documenti di incarico non previsti", "elenco_incarichi");
        }

        if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::DELEGATO)) {
            $esito = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->personaHaDelegatoOLegaleAttivoPerSoggetto($persona->getId(), $soggetto->getId(), TipoIncarico::LR);
            if ($esito) {
                return $this->addErrorRedirect("La persona selezionata è già legale rappresentante per il soggetto in gestione ", "elenco_incarichi");
            }
        }

        //mostro il form con i dati aggiuntivi
        $documentiIncarico = new DocumentiIncarico();
        if ($incaricoPersona->getTipoIncarico()->uguale(TipoIncarico::LR)) {
            $opzioni["DELEGA"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ATTO_NOMINA_LR);
            $opzioni["cf_firmatario"] = $persona->getCodiceFiscale();
        } else {
            $opzioni["DELEGA"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::DELEGA_DELEGATO);
            $lr = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->getLegaleRappresentante($soggetto);
            if (is_null($lr)) {
                return $this->addErrorRedirect("Prima di inserire una delega occorre avere un legale rappresentante attivo", "elenco_incarichi");
            }
            foreach ($lr as $lrsingolo) {
                $opzioni["cf_firmatario"][] = $lrsingolo->getCodiceFiscale();
            }
            $opzioni["cf_firmatario"][] = $persona->getCodiceFiscale();
        }
        $opzioni["url_indietro"] = $this->generateUrl("selezione_incaricato");
        if ($persona_inserita) {
            $opzioni["url_indietro"] = $this->generateUrl("crea_persona_incarico");
        }

        $form = $this->createForm("SoggettoBundle\Form\DocumentiIncaricoType", $documentiIncarico, $opzioni);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $soggetto = $this->getEm()->merge($soggetto);
                $tipoIncarico = $this->getEm()->merge($incaricoPersona->getTipoIncarico());

                $_incaricoPersona = new IncaricoPersona();
                $_incaricoPersona->setTipoIncarico($tipoIncarico);
                $_incaricoPersona->setSoggetto($soggetto);
                $_incaricoPersona->setIncaricato($persona);
                $_incaricoPersona->setStato($this->trovaDaCostante(new StatoIncarico(), StatoIncarico::ATTIVO));

                $nomina = $documentiIncarico->getFileNomina();
                $ci = $documentiIncarico->getFileCartaIdentita();
                $ciLr = $documentiIncarico->getFileCartaIdentitaLr();

                $documentoNomina = $this->get("documenti")->carica($nomina);
                $documentoCi = $this->get("documenti")->carica($ci);
                $documentoCiLr = $this->get("documenti")->carica($ciLr);

                $persona->setCartaIdentita($documentoCi);
                $_incaricoPersona->setDocumentoNomina($documentoNomina);
                $_incaricoPersona->setCartaIdentitaLr($documentoCiLr);

                $this->getEm()->persist($_incaricoPersona);
                $this->getEm()->flush();
                $this->addFlash("success", "Incarico correttamente aggiunto");
//				if (!$this->invioEmailRichiestaIncarico($_incaricoPersona, $this->getUser()) || !$this->invioEmailRichiestaIncaricoPa($_incaricoPersona, $this->getUser())) {
//					$this->addFlash("warning", "Richiesta di incarico creata correttamente, ma non è stato possibile inviare la email");
//				}
                return $this->redirectToRoute("elenco_incarichi");
            }
        }

        return array("form" => $form->createView());
    }

    /**
     * @Route("/persona_incarico_crea", name="crea_persona_incarico")
     * @PaginaInfo(titolo="Nuova Persona", sottoTitolo="")
     * @Menuitem(menuAttivo="selezioneSoggetti")
     */
    public function creaPersonaIncaricoAction() {
        $csrfTokenManager = $this->get("security.csrf.token_manager");
        $token = $csrfTokenManager->getToken("token")->getValue();
        return $this->get('inserimento_persona')->inserisciPersona($this->generateUrl("selezione_incaricato"), 'associa_incaricato', ['_token' => $token]);
    }

    protected function invioEmailRichiestaIncarico($_incaricoPersona, $utente) {
        //bisogna aggiungre la mailing list

        $to = [];
        $to[] = $utente->getEmail();

        $subject = "Sfinge2020: richiesta ruolo";
        $parametriView = ["incarico" => $_incaricoPersona, 'utente' => $utente];
        $renderViewTwig = "SoggettoBundle:Incarico:richiestaApprovazioneIncarico.email.html.twig";
        $noHtmlViewTwig = "SoggettoBundle:Incarico:richiestaApprovazioneIncarico.email.twig";

        try {
            $esito = $this->get("messaggi.email")->inviaEmail($to, '', $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo = null);
            if (!$esito->res) {
                $this->addFlash('danger', "Non è stato possibile inoltrare la Email : " . $esito->error);
                return false;
            }
            //$this->addFlash('success', "Email inviata con successo");
            return true;
        } catch (\Exception $e) {
            $this->addFlash('danger', "Non è stato possibile inoltrare la Email : " . $e->getMEssage());
            return false;
        }
    }

    protected function invioEmailRichiestaIncaricoPa($_incaricoPersona, $utente) {
        $to = [];
        $to[] = $this->container->getParameter("indirizzo.email.approvaincarichi");

        $subject = "Sfinge2020: richiesta ruolo";
        $parametriView = ["incarico" => $_incaricoPersona, 'utente' => $utente];
        $renderViewTwig = "SoggettoBundle:Incarico:richiestaApprovazioneIncaricoPa.email.html.twig";
        $noHtmlViewTwig = "SoggettoBundle:Incarico:richiestaApprovazioneIncaricoPa.email.twig";

        try {
            $esito = $this->get("messaggi.email")->inviaEmail($to, '', $subject, $renderViewTwig, $parametriView, $noHtmlViewTwig, $indirizzoAggiuntivo = null);
            if (!$esito->res) {
                $this->addFlash('danger', "Non è stato possibile inoltrare la Email : " . $esito->error);
                return false;
            }
            $this->addFlash('success', "Email inviata con successo alla PA");
            return true;
        } catch (\Exception $e) {
            $this->addFlash('danger', "Non è stato possibile inoltrare la Email : " . $e->getMEssage());
            return false;
        }
    }

    /**
     * @Route("/seleziona_operatori_richiesta/{richiesta_id}", name="seleziona_operatori_richiesta")
     * @PaginaInfo(titolo="Gestisci operatori richiesta", sottoTitolo="pagina per la gestione degli operatori richiesta")
     * @Template("SoggettoBundle:Incarico:elencoIncarichiRichiesta.html.twig")
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" : "richiesta_id"})
     * @Breadcrumb(elementi={
     * 	@ElementoBreadcrumb(testo="Elenco richieste", route="elenco_richieste"),
     * 	@ElementoBreadcrumb(testo="Gestisci operatori richiesta")
     * 	})
     * @Menuitem(menuAttivo="selezioneSoggetti")
     */
    public function elencoOperatoriRichiesta($richiesta_id) {

        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->findOneById($richiesta_id);
        $soggetto = $richiesta->getMandatario()->getSoggetto();
        if (is_null($soggetto)) {
            return $this->addErrorRedirect("Soggetto non specificato", "elenco_richieste");
        }
        if (!$this->isUtentePrincipale()) {
            return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico progetto", "elenco_richieste");
        }
        $incaricabili = $soggetto->getIncarichiProgetto();

        return ["incaricabili" => $incaricabili, "soggetto_id" => $soggetto->getId(), "richiesta" => $richiesta];
    }

    /**
     * @Route("/associa_operatore_richiesta/{incarico_id}/{richiesta_id}",  name="associa_operatore_richiesta")
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "incarico_id"})
     * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "incarico_id"})
     */
    public function associaOperatoreRichiestaAction($incarico_id, $richiesta_id) {
        if (!$this->isUtentePrincipale()) {
            return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico", "elenco_richieste");
        }
        $this->get('base')->checkCsrf('token');
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->findOneById($richiesta_id);
        $incarico = $this->getEm()->getRepository("SoggettoBundle\Entity\IncaricoPersona")->findOneById($incarico_id);

        $incaricoRichiesta = new IncaricoPersonaRichiesta($richiesta);

        try {
            $incaricoRichiesta->setIncaricoPersona($incarico);
            $this->getEm()->persist($incaricoRichiesta);
            $this->getEm()->flush();
            $this->addFlash("success", "Operatore correttamente associato");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute("seleziona_operatori_richiesta", array('richiesta_id' => $richiesta_id));
    }

    /**
     * @Route("/rimuovi_operatore_richiesta/{incarico_id}/{richiesta_id}",  name="rimuovi_operatore_richiesta")
     * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "incarico_id"})
     * @ControlloAccesso(contesto="incaricoPersona", classe="SoggettoBundle:IncaricoPersona", opzioni={"id" : "incarico_id"})
     */
    public function rimuoviOperatoreRichiestaAction($incarico_id, $richiesta_id) {
        if (!$this->isUtentePrincipale()) {
            return $this->addErrorRedirect("Solo l'utente principale può creare un nuovo incarico", "elenco_richieste");
        }
        $this->get('base')->checkCsrf('token');
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->findOneById($richiesta_id);
        $incarico = $this->getEm()->getRepository("SoggettoBundle\Entity\IncaricoPersona")->findOneById($incarico_id);
        $incaricoP = $this->getEm()->getRepository("SoggettoBundle\Entity\IncaricoPersonaRichiesta")->findOneBy(array('incarico_persona' => $incarico, 'richiesta' => $richiesta));

        try {
            $this->getEm()->remove($incaricoP);
            $this->getEm()->flush();
            $this->addFlash("success", "Operatore correttamente rimosso");
        } catch (\Exception $e) {
            $this->getEm()->rollback();
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute("seleziona_operatori_richiesta", array('richiesta_id' => $richiesta_id));
    }

}
