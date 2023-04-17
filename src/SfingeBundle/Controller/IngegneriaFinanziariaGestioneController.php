<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use SfingeBundle\Entity\IngegneriaFinanziaria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class IngegneriaFinanziariaGestioneController extends BaseController {
    /**
     * @Route("/crea_ingegneria_finanziaria", name="crea_ingegneria_finanziaria")
     * @Template("SfingeBundle:Procedura:ingegneria_finanziaria.html.twig")
     * @Menuitem(menuAttivo="creaIngegneriaFinanziaria")
     * @PaginaInfo(titolo="Nuova Ingegneria Finanziaria", sottoTitolo="pagina per creare una nuova ingegneria finanziaria")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Crea ingegneria finanziaria")})
     */
    public function creaIngegneriaAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $ingegneria_finanziaria = new IngegneriaFinanziaria();
        $fase = $this->getEm()->getRepository("SfingeBundle:Fase")->findOneByCodice("PRE");
        $stato = $this->getEm()->getRepository("SfingeBundle:StatoProcedura")->findOneByCodice("CONCLUSO");
        $ingegneria_finanziaria->setFase($fase);
        $ingegneria_finanziaria->setStatoProcedura($stato);
        $ingegneria_finanziaria->setVisibileInCorso(true);
        $ingegneria_finanziaria->setModalitaFinanziamentoAttiva(false);
        $ingegneria_finanziaria->setRendicontazioneAttiva(true);

        // ad oggi una sola tipologia generica a cui è collegata l'unica checklist pagamento prevista
        $tipo_ingegneria_finanziaria = $em->getRepository('SfingeBundle:TipoIngegneriaFinanziaria')->findOneBy(['codice' => '1']);
        $ingegneria_finanziaria->setTipoIngegneriaFinanziaria($tipo_ingegneria_finanziaria);

        $documentoConvenzione = new DocumentoFile();
        $ingegneria_finanziaria->setDocumentoConvenzione($documentoConvenzione);

        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser(), ['A3', 'A4']);

        if (0 == count($assi)) {
            $this->addFlash('error', "Nessun asse associato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $stato = $this->getEm()->getRepository("SfingeBundle:StatoProcedura")->findOneByCodice("CONCLUSO");
        $ingegneria_finanziaria->setStatoProcedura($stato);
        $ingegneria_finanziaria->setNumeroRichieste(1);

        $options["assi"] = $assi;
        $options["disabled"] = false;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        $options["documento_opzionale"] = false;
        $options["TIPOLOGIA_DOCUMENTO"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ALTRO);

        $form = $this->createForm('SfingeBundle\Form\IngegneriaFinanziariaType', $ingegneria_finanziaria, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->get("documenti")->carica($documentoConvenzione);
                $em->persist($ingegneria_finanziaria);
                $em->flush();
                $this->addFlash('success', "Modifiche salvate correttamente");

                return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["ingegneria_finanziaria"] = $ingegneria_finanziaria;
        $form_params["lettura"] = false;
        return $form_params;
    }

    /**
     * @Route("/modifica_ingegneria_finanziaria/{id_ingegneria_finanziaria}", name="modifica_ingegneria_finanziaria")
     * @Template("SfingeBundle:Procedura:ingegneria_finanziaria.html.twig")
     * @PaginaInfo(titolo="Modifica Ingegneria Finanziaria", sottoTitolo="pagina per modificare i dati dell'ingegneria finanziaria selezionata")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="modifica ingegneria finanziaria")})
     * @param mixed $id_ingegneria_finanziaria
     */
    public function modificaIngegneriaFinanziariaAction($id_ingegneria_finanziaria) {
        $em = $this->getDoctrine()->getManager();
        $ingegneria_finanziaria = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_ingegneria_finanziaria);
        if (\is_null($ingegneria_finanziaria)) {
            $this->addFlash('error', "Ingegneria finanziaria non trovata");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $request = $this->getCurrentRequest();

        $vecchioDocumento = $ingegneria_finanziaria->getDocumentoConvenzione();
        $documentoConvenzione = new DocumentoFile();
        $ingegneria_finanziaria->setDocumentoConvenzione($documentoConvenzione);

        $assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser(), ['A3', 'A4']);

        if (0 == count($assi)) {
            $this->addFlash('error', "Nessun asse associato");
            return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
        }

        $options["assi"] = $assi;

        $options["disabled"] = false;
        $options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");

        $options["documento_opzionale"] = true;
        $options["TIPOLOGIA_DOCUMENTO"] = $this->trovaDaCostante("DocumentoBundle:TipologiaDocumento", TipologiaDocumento::ALTRO);

        $form = $this->createForm('SfingeBundle\Form\IngegneriaFinanziariaType', $ingegneria_finanziaria, $options);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Se non ho inserito un nuovo documento ri-associo quello vecchio.
                if (!is_null($ingegneria_finanziaria->getDocumentoConvenzione()->getFile())) {
                    $this->get("documenti")->carica($ingegneria_finanziaria->getDocumentoConvenzione());
                    // cancello il vecchio file
                    $vecchioDocumento->setDataCancellazione(new \DateTime());
                } else {
                    $ingegneria_finanziaria->setDocumentoConvenzione($vecchioDocumento);
                }
                $em->persist($ingegneria_finanziaria);
                $em->flush();
                $this->addFlash('success', "Modifiche salvate correttamente");

                return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $form_params["form"] = $form->createView();
        $form_params["ingegneria_finanziaria"] = $ingegneria_finanziaria;
        $form_params["lettura"] = true;
        $form_params["vecchioDocumento"] = $vecchioDocumento;

        return $form_params;
    }
}
