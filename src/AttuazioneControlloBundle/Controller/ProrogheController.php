<?php

namespace AttuazioneControlloBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use BaseBundle\Annotation\ControlloAccesso;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use BaseBundle\Exception\SfingeException;
use AttuazioneControlloBundle\Entity\StatoProroga;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\DocumentoFile;

/**
 * @Route("/beneficiario/proroghe")
 */
class ProrogheController extends \BaseBundle\Controller\BaseController {

    /**
     * @Route("/{id_richiesta}/elenco", name="elenco_proroghe")
     * @Template()
     * @PaginaInfo(titolo="Elenco proroghe progetto",sottoTitolo="mostra l'elenco delle proroghe richieste per un progetto")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario"),
     *                       @ElementoBreadcrumb(testo="elenco proroghe progetto")})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     */
    public function elencoProrogheAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        $attuazione = $richiesta->getAttuazioneControllo();
        $proroghe = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->findBy(array("attuazione_controllo_richiesta" => $attuazione));
        return array("proroghe" => $proroghe, "richiesta" => $richiesta);
    }

    /**
     * @Route("/{id_richiesta}/aggiungi", name="aggiungi_proroga")
     * @PaginaInfo(titolo="Richiedi proroga",sottoTitolo="pagina per la richiesta di una proroga")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco progetti", route="elenco_gestione_beneficiario"),
     *                       @ElementoBreadcrumb(testo="richiedi proroga")})
     * @ControlloAccesso(contesto="soggetto", classe="RichiesteBundle:Richiesta", opzioni={"id" = "id_richiesta"})
     */
    public function aggiungiProrogaAction($id_richiesta) {
        $richiesta = $this->getEm()->getRepository("RichiesteBundle\Entity\Richiesta")->find($id_richiesta);
        return $this->get("gestore_proroghe")->getGestore($richiesta->getProcedura())->aggiungiProroga($id_richiesta);
    }

    /**
     * @Route("/{id_proroga}/modifica", name="modifica_proroga")
     * @PaginaInfo(titolo="Modifica proroga",sottoTitolo="pagina per la modifica dei dati di una proroga")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})
     */
    public function modificaDatiProrogaAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        return $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->modificaDatiProroga($id_proroga);
    }

    /**
     * @Route("/{id_proroga}/dettaglio", name="dettaglio_proroga")
     * @PaginaInfo(titolo="Dettaglio proroga",sottoTitolo="pagina di riepilogo della richiesta di proroga")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})
     */
    public function dettaglioProrogaAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        return $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->dettaglioProroga($id_proroga);
    }

    /**
     * @Route("/{id_proroga}/valida_proroga", name="valida_proroga")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})
     */
    public function validaProrogaAction($id_proroga) {
        $this->get('base')->checkCsrf('token');
        try {
            $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
            $response = $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->validaProroga($id_proroga);
            return $response;
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_proroga", array("id_proroga" => $id_proroga));
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "dettaglio_proroga", array("id_proroga" => $id_proroga));
        }
    }

    /**
     * @Route("/{id_proroga}/elimina", name="elimina_proroga")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})
     */
    public function eliminaProrogaAction($id_proroga) {
        $this->get('base')->checkCsrf('token');
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        return $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->eliminaProroga($id_proroga);
    }

    /**
     * @Route("/{id_proroga}/invalida_proroga", name="invalida_proroga")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})
     */
    public function invalidaProrogaAction($id_proroga) {
        $this->get('base')->checkCsrf('token');
        try {
            $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
            $response = $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->invalidaProroga($id_proroga);
            return $response;
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_proroga", array("id_proroga" => $id_proroga));
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "dettaglio_proroga", array("id_proroga" => $id_proroga));
        }
    }

    /**
     *
     * @Route("/{id_proroga}/invia_proroga", name="invia_proroga")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})  
     */
    public function inviaProrogaAction($id_proroga) {
        $this->get('base')->checkCsrf('token');
        try {
            $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
            $response = $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->inviaProroga($id_proroga);
            return $response;
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "dettaglio_proroga", array("id_proroga" => $id_proroga));
        } catch (\Exception $e) {
            //mettere log
            return $this->addErrorRedirect("Errore generico", "dettaglio_proroga", array("id_proroga" => $id_proroga));
        }
    }

    /**
     *
     * @Route("/{id_proroga}/genera_pdf_proroga", name="genera_pdf_proroga")
     * @Method({"GET"})
     * @Menuitem(menuAttivo = "elencoRichieste")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})  
     */
    public function generaPdfProrogaAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        return $this->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->generaPdf($id_proroga);
    }

    /**
     * @Route("/{id_proroga}/scarica_proroga", name="scarica_proroga")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})  
     */
    public function scaricaProrogaAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);

        if (is_null($proroga->getDocumentoProroga())) {
            return $this->addErrorRedirect("Nessun documento associato alla proroga", "dettaglio_proroga", array("id_proroga" => $id_proroga));
        }

        return $this->get("documenti")->scaricaDaId($proroga->getDocumentoProroga()->getId());
    }

    /**
     * @Route("/{id_proroga}/carica_proroga_firmata", name="carica_proroga_firmata")
     * @PaginaInfo(titolo="Carica proroga firmato",sottoTitolo="pagina per caricare la proroga firmata")
     * @Template("AttuazioneControlloBundle:Proroghe:caricaProrogaFirmata.html.twig")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})  
     */
    public function caricaProrogaFirmataAction($id_proroga) {
        $em = $this->getEm();
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);
        $richiesta = $proroga->getRichiesta();
        $request = $this->getCurrentRequest();

        $documento_file = new DocumentoFile();

        if (!$proroga) {
            throw $this->createNotFoundException('Risorsa non trovata');
        }

        try {
            if (!$proroga->getStato()->uguale(StatoProroga::PROROGA_VALIDATA)) {
                throw new SfingeException("Stato non valido per effettuare l'operazione");
            }
        } catch (SfingeException $e) {
            return $this->addErrorRedirect($e->getMessage(), "elenco_proroghe");
        }

        $opzioni_form["tipo"] = TipologiaDocumento::RICHIESTA_PROROGA_FIRMATA;
        $opzioni_form["cf_firmatario"] = $proroga->getFirmatario()->getCodiceFiscale();
        $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documento_file, $opzioni_form);
        $form->add("pultanti", "BaseBundle\Form\SalvaIndietroType", array("url" => $this->generateUrl("dettaglio_proroga", array('id_proroga' => $id_proroga))));
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $this->container->get("documenti")->carica($documento_file, 0, $richiesta);
                    $proroga->setDocumentoProrogaFirmato($documento_file);
                    $this->get("sfinge.stati")->avanzaStato($proroga, StatoProroga::PROROGA_FIRMATA);
                    $em->flush();
                    return $this->addSuccessRedirect("Documento caricato correttamente", "dettaglio_proroga", array('id_proroga' => $id_proroga));
                } catch (\Exception $e) {
                    //TODO gestire cancellazione del file
                    $this->addFlash('error', "Errore generico");
                }
            }
        }
        $form_view = $form->createView();

        return array("id_proroga" => $id_proroga, "form" => $form_view);
    }

    /**
     * @Route("/{id_proroga}/scarica_proroga_firmata", name="scarica_proroga_firmata")
     * @Method({"GET"})
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})  
     */
    public function scaricaProrogaFirmataAction($id_proroga) {
        $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->find($id_proroga);

        if (is_null($proroga->getDocumentoProrogaFirmato())) {
            return $this->addErrorRedirect("Nessun documento associato alla proroga", "dettaglio_proroga", array('id_proroga' => $id_proroga));
        }

        return $this->get("documenti")->scaricaDaId($proroga->getDocumentoProrogaFirmato()->getId());
    }

    /**
     * @Route("/{id_proroga}/documenti_proroga", name="documenti_proroga")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:Proroga", opzioni={"id" = "id_proroga"})  
     * @PaginaInfo(titolo="Documentazione proroga",sottoTitolo="pagina per caricare la documentazione da allegare alla proroga")
     * @Menuitem(menuAttivo = "elencoRichiesteGestione")
     * @var integer|null $id_proroga
     */
    public function documentiProrogaAction($id_proroga) {
        // $proroga = $this->getEm()->getRepository("AttuazioneControlloBundle\Entity\Proroga")->findOneById($id_proroga);
        $dql = 'select proroga, atc, richiesta, procedura, documenti '
                . 'from AttuazioneControlloBundle:Proroga proroga '
                . 'join proroga.attuazione_controllo_richiesta atc '
                . 'join atc.richiesta richiesta '
                . 'join richiesta.procedura procedura '
                . 'left join proroga.documenti documenti '
                . 'where proroga.id = :id_proroga and proroga.data_cancellazione is null';

        $proroga = $this->getEm()->createQuery($dql)->setParameter('id_proroga', $id_proroga)->getOneOrNullResult();

        if (\is_null($proroga)) {
            throw new SfingeException('La proroga in esame non è accessibile');
        }
        return $this->container->get("gestore_proroghe")->getGestore($proroga->getRichiesta()->getProcedura())->elencoDocumentiProroga($proroga);
    }

    /**
     * @Route("/elimina_documentazione_proroga/{id_documento_proroga}", name="elimina_documentazione_proroga")
     * @ControlloAccesso(contesto="soggetto", classe="AttuazioneControlloBundle:DocumentoProroga", opzioni={"id" = "id_documento_proroga"})  
     * @var integer|null $id_proroga
     */
    public function eliminaDocumentazioneProrogaAction($id_documento_proroga) {
        $request = $this->getCurrentRequest();
        $this->get('base')->checkCsrf('token');
        $em = $this->getEm();
        $proroga = $em->getRepository('AttuazioneControlloBundle:DocumentoProroga')->findOneById($id_documento_proroga)->getProroga();
        if (\is_null($proroga)) {
            throw new SfingeException('Documento non trovato');
        }
        $dql = 'delete '
                . 'from AttuazioneControlloBundle:DocumentoProroga documento_proroga '
                . 'where documento_proroga.id = :id_documento_proroga ';

        try {
            $em->createQuery($dql)
                    ->setParameter('id_documento_proroga', $id_documento_proroga)
                    ->execute();

            $this->addSuccess('Operazione effettuata con successo');
        } catch (\Exception $e) {
            $this->get('monolog.logger.schema31')->error($e->getMessage(), array(
                'id_documento_proroga' => $id_documento_proroga,
            ));
            $this->addError("Errore durante l'operazione");
        }
        return $this->redirectToRoute(
                        'documenti_proroga',
                        array('id_proroga' => $proroga->getId()));
    }

}
