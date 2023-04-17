<?php

namespace ProtocollazioneBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use RichiesteBundle\Service\GestoreResponse;

class DocERProtocollazioneController extends \BaseBundle\Controller\BaseController {

    /**
     * @Route("/tipologia", name="tipologia")
     */
    public function tipologiaAction() {
        $titolo = "Richieste di protocollazione";
        $sottotitolo = "gestione della tipologia di richiesta";

        //Leggo il numero di richieste di protocollazione pendenti 
        //(fase <> 0) raggruppate per processo
        $em = $this->getDoctrine()->getManager();
        $righeDB = $em->getRepository('ProtocollazioneBundle:RichiestaProtocolloDocumento')
                ->contaRichiestePerProcesso();


        return $this->render('ProtocollazioneBundle:Protocollazione:protocollazione.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $sottotitolo,
                    'righe' => $righeDB
        ));
    }

    /**
     * @Route("/crea_richieste", name="crea_richieste")
     */
    public function creaRichiestaAction() {
        $titolo = "Creazione richieste di protocollazione";
        $sottotitolo = "gestione manuale delle richieste inviate alla PA";
        $service = $this->get('crea_richieste');

        $service->getRichiesteInviatePA();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $sottotitolo,
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/crea_variazioni", name="crea_variazioni")
     */
    public function creaVariazioneAction() {
        $titolo = "Creazione richieste di protocollazione";
        $sottotitolo = "gestione manuale delle variazioni inviate alla PA";
        $service = $this->get('crea_variazione');

        $service->getVariazioniInviatePA();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $sottotitolo,
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/domande_contributo", name="domande_contributo")
     */
    public function domandeContributoAction() {
        $titolo = "Protocollazione domande contributo";
        $service = $this->get('domande_contributo');

        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/variazioni", name="variazioni")
     */
    public function variazioniAction() {
        $titolo = "Protocollazione variazione";
        $service = $this->get('variazioni');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/integrazioni_istruttoria", name="integrazioni_istruttoria")
     */
    public function integrazioniIstruttoriaAction() {
        $titolo = "Protocollazione uscita integrazione istruttoria";
        $service = $this->get('integrazioni_istruttoria');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/integrazioni_istruttoria_risposta", name="integrazioni_istruttoria_risposta")
     */
    public function integrazioniIstruttoriaRispostaAction() {
        $titolo = "Protocollazione integrazione istruttoria";
        $service = $this->get('integrazioni_istruttoria_risposta');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/pagamenti", name="pagamenti")
     */
    public function pagamentiAction() {
        $titolo = "Protocollazione comunicazioni";
        $service = $this->get('pagamenti');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig',
                        array(
                            'titolo' => $titolo,
                            'sottoTitolo' => $service->getSottotitolo(),
                            'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/integrazioni_pagamento", name="integrazioni_pagamento")
     */
    public function integrazioniPagamentoAction() {
        $titolo = "Protocollazione integrazione pagamento";
        $service = $this->get('integrazioni_pagamento');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/integrazioni_pagamento_risposta", name="integrazioni_pagamento_risposta")
     */
    public function integrazioniPagamentoRispostaAction() {
        $titolo = "Protocollazione risposta integrazione pagamento";
        $service = $this->get('integrazioni_pagamento_risposta');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/comunicazioni_esiti_richiesta", name="comunicazioni_esiti_richiesta")
     */
    public function comunicazioniEsitiRichiestaAction() {
        $titolo = "Protocollazione comuncazione di esito istruttoria richiesta";
        $service = $this->get('comunicazione_esito_istruttoria');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/esito_istruttoria_pagamento", name="esito_istruttoria_pagamento")
     */
    public function esitoIstruttoriaPagamentoAction() {
        $titolo = "Protocollazione esito istruttoria pagamento";
        $service = $this->get('esito_istruttoria_pagamento');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/comunicazioni_esiti_richiesta_risposta", name="comunicazioni_esiti_richiesta_risposta")
     */
    public function comunicazioniEsitiRichiestaRispostaAction() {
        $titolo = "Protocollazione risposta comuncazione di esito istruttoria richiesta";
        $service = $this->get('comunicazione_esito_istruttoria_risposta');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/richiesta_chiarimenti", name="richiesta_chiarimenti")
     */
    public function richiestaChiarimentiAction() {
        $titolo = "Protocollazione richiesta chiarimenti";
        $service = $this->get('richiesta_chiarimenti');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/risposta_richiesta_chiarimenti", name="risposta_richiesta_chiarimenti")
     */
    public function rispostaRichiestaChiarimentiAction() {
        $titolo = "Protocollazione risposta richiesta chiarimenti";
        $service = $this->get('risposta_richiesta_chiarimenti');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/proroga_progetto", name="proroga_progetto")
     */
    public function prorogaProgettoAction() {
        $titolo = "Protocollazione proroga progetto";
        $service = $this->get('proroga_progetto');

        $service->elabora();

        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/comunicazioni_progetto_pa", name="comunicazioni_progetto_pa")
     */
    public function comunicazioniProgettoPaAction() {
        $titolo = "Cron che serve per la protocollazione della comunicazione progetto";
        $service = $this->get('comunicazione_progetto_pa');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/comunicazioni_progetto_risposta", name="comunicazioni_progetto_risposta")
     */
    public function comunicazioniProgettoRispostaAction() {
        $titolo = "Cron che serve per la protocollazione della comunicazione progetto";
        $service = $this->get('comunicazione_progetto_risposta');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/comunicazione_attuazione_pa", name="comunicazione_attuazione_pa")
     */
    public function comunicazioniAttuazionePaAction() {
        $titolo = "Cron che serve per la protocollazione della comunicazione attuazione";
        $service = $this->get('comunicazione_attuazione_pa');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }

    /**
     * @Route("/comunicazione_attuazione_risposta", name="comunicazione_attuazione_risposta")
     */
    public function comunicazioniAttuazioneRispostaAction() {
        $titolo = "Cron che serve per la protocollazione della comunicazione attuazione";
        $service = $this->get('comunicazione_attuazione_risposta');
        $service->elabora();
        return $this->render('ProtocollazioneBundle:Messaggi:messaggi.html.twig', array(
                    'titolo' => $titolo,
                    'sottoTitolo' => $service->getSottotitolo(),
                    'messaggi' => $service->getMsg_array()
        ));
    }
    
     /**
     * @Route("/dati_protocollo_manuale/{id_richiesta_protocollo}", name="dati_protocollo_manuale")
     */
    public function gestioneDatiProtocolloAction($id_richiesta_protocollo) {
        $response = $this->datiProtocollo($id_richiesta_protocollo);
        return $response->getResponse();
    }
    
    public function datiProtocollo($id_richiesta_protocollo) {
        $em = $this->getEm();
        $richiesta_protocollo = $em->getRepository("ProtocollazioneBundle:RichiestaProtocollo")->find($id_richiesta_protocollo);
        $request = $this->getCurrentRequest();
        $opzioni['url_indietro'] = $this->generateUrl("tipologia");

        $form = $this->createForm("ProtocollazioneBundle\Form\DatiProtocolloType", $richiesta_protocollo, $opzioni);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $richiesta_protocollo = $em->getRepository("ProtocollazioneBundle:RichiestaProtocollo")->findOneById(array($id_richiesta_protocollo));
                if($richiesta_protocollo->getFase() != 5 ) {
                    return new GestoreResponse($this->addErrorRedirect("Fase non compatibile per modifica", "tipologia"));
                }
                $em = $this->getEm();
                try {

                    $em->beginTransaction();
                    $richiesta_protocollo->setFase(6);
                    $richiesta_protocollo->setDataPg(new \DateTime());
                    $em->persist($richiesta_protocollo);

                    $em->flush();
                    $em->commit();

                    return new GestoreResponse($this->addSuccessRedirect("Dati del protocollo modificati correttamente", "tipologia"));
                } catch (\Exception $e) {
                    $em->rollback();
                    throw new SfingeException("Dati del protocollo non modificati");
                }
            }
        }

        $dati = array("id_richiesta_protocollo" => $richiesta_protocollo->getId(), "form" => $form->createView());

        $response = $this->render("ProtocollazioneBundle:Protocollazione:datiProtocollo.html.twig", $dati);

        return new GestoreResponse($response, "ProtocollazioneBundle:Protocollazione:datiProtocollo.html.twig", $dati);
    }



}
