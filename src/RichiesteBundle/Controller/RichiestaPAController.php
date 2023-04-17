<?php

namespace RichiesteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Ricerche\RicercaProcedurePA;
use BaseBundle\Exception\SfingeException;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use SoggettoBundle\Entity\Soggetto;

/**
 * @Route("/procedura_pa")
 */
class RichiestaPAController extends AbstractController {
    /**
     * @Route("/elenco/{sort}/{direction}/{page}", defaults={"sort" : "i.id", "direction" : "asc", "page" : "1"}, name="procedura_pa_elenco")
     * @PaginaInfo(titolo="Elenco bandi", sottoTitolo="mostra l'elenco delle procedure PA disponibili")
     * @Menuitem(menuAttivo="procedure-pa-elenco")
     */
    public function elencoAction() {
        $filtroRicerca = new RicercaProcedurePA();
        $filtroRicerca->setUtente($this->getUser());
        $risultato = $this->get('ricerca')->ricerca($filtroRicerca);

        $params = [
            'richieste' => $risultato['risultato'],
            'form_ricerca_richieste' => $risultato['form_ricerca'],
            'filtro_attivo' => $risultato['filtro_attivo'],
        ];

        return $this->render('RichiesteBundle:ProcedurePA:elencoRichieste.html.twig', $params);
    }

    /**
     * @Route("/elenco_pulisci", name="procedura_pa_elenco_pulisci")
     * @Menuitem(menuAttivo="procedure-pa-elenco")
     */
    public function elencoPulisciAction() {
        $filtroRicerca = new RicercaProcedurePA();
        $filtroRicerca->setUtente($this->getUser());
        $this->get('ricerca')->pulisci($filtroRicerca);

        return $this->redirectToRoute('procedura_pa_elenco');
    }

    /**
     * @Route("/inserisci", name="procedura_pa_inserisci")
     * @PaginaInfo(titolo="Elenco bandi", sottoTitolo="mostra l'elenco delle procedure PA disponibili")
     * @Menuitem(menuAttivo="procedure-pa-inserisci")
     */
    public function selezionaBandoAction() {
        $em = $this->getEm();
        $proponente = new \RichiesteBundle\Entity\Proponente();
        $proponente->setMandatario(true);

        $this->creaRichiesta();
        $this->richiesta->setAbilitaGestioneBandoChiuso(false);

        $proponente->setRichiesta($this->richiesta);
        $this->richiesta->addProponenti($proponente);

        $procedure = $em->getRepository('SfingeBundle:Procedura')->getElencoProcedurePA($this->getUser());

        $form = $this->createForm('RichiesteBundle\Form\SelezionaProceduraPAType', $this->richiesta, ['procedure' => $procedure]);
        $form->handleRequest($this->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $connection = $em->getConnection();
            $proponente->setDimensioneImpresa($this->richiesta->getSoggetto()->getDimensioneImpresa());
            try {
                $this->setInfoRichiestaDaProcedura();
                $this->controllaValiditaRichiesta($this->richiesta);

                $connection->beginTransaction();
                $em->persist($this->richiesta);
                $em->persist($proponente);
                $em->flush();
                $connection->commit();

                return $this->addSuccessRedirect('Richiesta creata correttamente', 'procedura_pa_nuova_richiesta', ['id_richiesta' => $this->richiesta->getId()]);
            } catch (SfingeException $e) {
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->get('logger')->error($e->getMessage());
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                if ($connection->isTransactionActive()) {
                    $connection->rollBack();
                }
                $this->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }

        return $this->render('RichiesteBundle:ProcedurePA:selezionaProcedura.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @throws SfingeException
     */
    private function controllaValiditaRichiesta(Richiesta $richiesta) {
        $soggetto = $richiesta->getSoggetto();
        $this->verificaLegaleRappresentante($soggetto);
        $this->verificaNumeroRichieste($richiesta);
    }

    /**
     * @throws SfingeException
     */
    private function verificaLegaleRappresentante(Soggetto $soggetto) {
        $legaleRappresentante = $this->getEm()->getRepository('SoggettoBundle:Soggetto')->getLegaleRappresentante($soggetto);
        if (count($legaleRappresentante) == 0) {
            $legaleRapprDaConfermare = $this->getEm()->getRepository("SoggettoBundle\Entity\Soggetto")->getLegaleRappresentanteDaConfermare($soggetto);
            if (count($legaleRapprDaConfermare) != 0) {
                throw new SfingeException('Non risulta un legale rappresentante attivo');
            }
            throw new SfingeException('Non è stato inserito alcun legale rappresentate');
        }
    }

    /**
     * @throws SfingeException
     */
    private function verificaNumeroRichieste(Richiesta $richiesta) {
        $procedura = $richiesta->getProcedura();
        $richiesteDaSoggetto = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")
            ->getRichiesteDaSoggetto(
                $richiesta->getSoggetto()->getId(),
                $procedura->getId(),
                null);

        if (count($richiesteDaSoggetto) > 0) {
            if (true == $procedura->getAbilitaReinvioNonAmmesse()) {
                if (count($richiesteDaSoggetto) >= $procedura->getNumeroRichieste()) {
                    $sospese = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->hasRichiesteAmmesseInIstruttoria($richiesteDaSoggetto);
                    if ($sospese) {
                        throw new SfingeException('È già stato raggiunto il numero di richieste ammesso per la procedura / bando selezionato');
                    }
                }
            } elseif (count($richiesteDaSoggetto) >= $procedura->getNumeroRichieste()) {
                throw new SfingeException('È già stato raggiunto il numero di richieste ammesso per la procedura / bando selezionato');
            }
        }
    }

    /**
     * @Route("/{id_richiesta}/nuova_richiesta", name="procedura_pa_nuova_richiesta")
     * @PaginaInfo(titolo="Elenco bandi", sottoTitolo="mostra l'elenco dei bandi disponibili")
     * @Menuitem(menuAttivo="procedure-pa-inserisci")
     * @param mixed $id_richiesta
     */
    public function nuovaRichiestaAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);

        return $this->getGestoreRichiestaPA($richiesta)->nuovaRichiesta();
    }

    /**
     * @Route("/{id_richiesta}/dettaglio", name="procedura_pa_dettaglio_richiesta")
     * @PaginaInfo(titolo="Richiesta", sottoTitolo="pagina con le sezioni della richiesta da compilare")
     * @Breadcrumb(elementi={
     * 				@ElementoBreadcrumb(testo="Elenco richieste", route="procedura_pa_elenco"),
     * 				@ElementoBreadcrumb(testo="Dettaglio richiesta")
     * 				})
     * @Menuitem(menuAttivo="procedure-pa-elenco")
     * @param mixed $id_richiesta
     */
    public function dettaglioRichiestaAction($id_richiesta) {
        $richiesta = $this->getRichiesta($id_richiesta);

        return $this->getGestoreRichiestaPA($richiesta)->dettaglioRichiesta();
    }

    /**
     * @Route("/{id_richiesta}/sezione/{nome_sezione}/{parametro1}/{parametro2}/{parametro3}/{parametro4}",
     * name="procedura_pa_sezione", defaults={"parametro1" : NULL, "parametro2" : NULL, "parametro3" : NULL, "parametro4" : NULL})
     * @Menuitem(menuAttivo="procedure-pa-elenco")
     * @param mixed $id_richiesta
     * @param mixed $nome_sezione
     * @param mixed $parametro1
     * @param mixed $parametro2
     * @param mixed $parametro3
     * @param mixed $parametro4
     */
    public function sezioneAction($id_richiesta, $nome_sezione, $parametro1, $parametro2, $parametro3, $parametro4) {
        $parametri = \array_slice(\func_get_args(), 2);
        $richiesta = $this->getRichiesta($id_richiesta);
        $gestore = $this->getGestoreRichiestaPA($richiesta);

        return $gestore->visualizzaSezione($nome_sezione, $parametri);
    }

    /**
     * @Route("/{id_richiesta}/azione/{nome_azione}", name="procedura_pa_azione")
     * @Menuitem(menuAttivo="procedure-pa-elenco")
     * @param mixed $id_richiesta
     * @param mixed $nome_azione
     */
    public function azioneAction($id_richiesta, $nome_azione) {
        $richiesta = $this->getRichiesta($id_richiesta);

        return $this->getGestoreRichiestaPA($richiesta)->risultatoAzione($nome_azione);
    }

    /**
     * @Route("/search_soggetto", name="procedura_pa_search_azienda")
     */
    public function searchSoggettoAction() {
        $request = $this->getCurrentRequest();
        $q = $request->query->get('q');
        if (\is_null($q)) {
            return new Response('Richiesta non valida', Response::HTTP_BAD_REQUEST);
        }
        //Perchè solo aziende ??
        //$res = $this->getEm()->getRepository('SoggettoBundle:Azienda')->searchAzienda($q);
        $res = $this->getEm()->getRepository('SoggettoBundle:Soggetto')->searchSoggetto($q);

        return new JsonResponse($res);
    }

    /**
     * @return \RichiesteBundle\Service\IGestoreRichiestaPA
     *
     * @throws \Exception
     * @param mixed $richiesta
     */
    protected function getGestoreRichiestaPA($richiesta) {
        return $this->container->get('gestore_richiesta_pa')->getGestore($richiesta);
    }

    /**
     * @Route("/questionario/{id_istanza_pagina}/{id_pagina}/{id_istanza_frammento}/{azione}", name="questionario_richiesta_pa", defaults={"id_istanza_pagina" : "-", "id_pagina" : "-", "id_istanza_frammento" : "-", "azione" : "modifica"})
     * @param mixed $id_istanza_pagina
     * @param mixed $id_pagina
     * @param mixed $id_istanza_frammento
     * @param mixed $azione
     */
    public function questionarioAction(\Symfony\Component\HttpFoundation\Request $request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione) {
        if ("-" != $id_istanza_pagina) {
            $istanza_pagina = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaPagina")->find($id_istanza_pagina);
        } else {
            $istanza_frammento = $this->getEm()->getRepository("FascicoloBundle\Entity\IstanzaFrammento")->find($id_istanza_frammento);
            $istanza_pagina = $istanza_frammento->getIstanzaPagina();
        }

        $istanza_fascicolo = $istanza_pagina->getIstanzaFascicolo();
        $oggetto_richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\OggettoRichiesta")->findOneBy(["istanza_fascicolo" => $istanza_fascicolo]);

        $richiesta = $oggetto_richiesta->getRichiesta();
        $id_richiesta = $richiesta->getId();

        if ('PROCEDURA_PA' != $richiesta->getProcedura()->getTipo()) {
            throw new SfingeException('Accesso negato');
        }

        $isRichiestaDisabilitata = $this->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata($id_richiesta);

        if ($isRichiestaDisabilitata) {
            $azione = "visualizza";
        }

        $contestoSoggetto = $this->get('contesto')->getContestoRisorsa($richiesta, "soggetto");
        $accessoConsentito = $this->isGranted(\SoggettoBundle\Security\SoggettoVoter::ALL, $contestoSoggetto);

        $contestoProcedura = $this->get('contesto')->getContestoRisorsa($richiesta, "procedura");
        $accessoConsentito |= $this->isGranted(\SfingeBundle\Security\ProceduraVoter::READ, $contestoProcedura);

        if (!$accessoConsentito) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        $this->container->get("pagina")->setMenuAttivo("elencoRichieste", $this->getSession());
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Elenco richieste", $this->generateUrl("procedura_pa_elenco"));
        $this->container->get("pagina")->aggiungiElementoBreadcrumb("Dettaglio richiesta", $this->generateUrl("procedura_pa_dettaglio_richiesta", ["id_richiesta" => $id_richiesta]));

        $this->getSession()->set("fascicolo.route_istanza_pagina", "questionario_richiesta_pa");

        return $this->get("fascicolo.istanza")->istanzaPagina($request, $id_istanza_pagina, $id_pagina, $id_istanza_frammento, $azione);
    }
}
