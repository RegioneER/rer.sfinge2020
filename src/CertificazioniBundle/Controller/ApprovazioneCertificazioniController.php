<?php

namespace CertificazioniBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use BaseBundle\Annotation\ControlloAccesso;
use CertificazioniBundle\Entity\StatoCertificazione;
use CertificazioneBundle\Entity\Certificazione;
use AttuazioneControlloBundle\Entity\RichiestaSpesaCertificata;
use AttuazioneControlloBundle\Entity;
use DocumentoBundle\Component\ResponseException;

/**
 * @Route("/approvazione")
 */
class ApprovazioneCertificazioniController extends BaseController {
    
    
    /**
     * @Route("/{id_certificazione}/{id_certificazione_pagamento}/valuta_certificazione_pagamento", name="valuta_certificazione_pagamento")
     * @PaginaInfo(titolo="Valutazione pagamento",sottoTitolo="pagina di valutazione del pagamento")
     * @Menuitem(menuAttivo = "elencoCertificazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_pagamenti_certificati", parametri={"id_certificazione"}),
     *                       @ElementoBreadcrumb(testo="Valuta pagamento")})
     * 
     */
    public function valutaCertificazionePagamentoAction($id_certificazione_pagamento) {
        $pagamento = $this->getEm()->getRepository("CertificazioniBundle\Entity\CertificazionePagamento")->find($id_certificazione_pagamento);
        $certificazione = $pagamento->getCertificazione();

        $em = $this->getEm();
        $options = array();
        $options["url_indietro"] = $this->generateUrl("elenco_pagamenti_certificati", array("id_certificazione" => $certificazione->getId()));

        $form = $this->createForm("CertificazioniBundle\Form\ValutaCertificazionePagamentoType", $pagamento, $options);

        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);         
            if ($form->isValid()) {
                $em = $this->getEm();
                try {     
                    $em->flush();
                    $this->addFlash("success", "Le informazioni sono state correttamente salvate");
                    return $this->redirectToRoute("elenco_pagamenti_certificati",array("id_certificazione" => $certificazione->getId()));
                } catch (\Exception $e) {
                    $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza. ".$e->getMessage());
                }
            }
        }

        $dati = array();
        $dati["form"] = $form->createView();

        return $this->render("CertificazioniBundle:Certificazioni:valutaCertificazionePagamento.html.twig", $dati);
    }

	/**
	 * @Route("/{id_certificazione}/valuta_certificazione", name="valuta_certificazione")
	 * @PaginaInfo(titolo="Valutazione certificazione",sottoTitolo="pagina di valutazione della certificazione")
	 * @Menuitem(menuAttivo = "elencoCertificazioni")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco certificazioni", route="elenco_certificazioni"),
     *                       @ElementoBreadcrumb(testo="Valuta certificazione")})
     * 
	 */
	public function valutaCertificazioneAction($id_certificazione) {
		$certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione);
        
        if (!$certificazione->isApprovabile()) {
            $this->addFlash("error", "Azione non compatile con lo stato della certificazione");
            return $this->redirectToRoute("elenco_certificazioni");            
        }
        
        return $this->render('CertificazioniBundle:Certificazioni:valutaCertificazione.html.twig', array('certificazione' => $certificazione));
	}   
    
	/**
	 * @Route("/{id_certificazione}/approva", name="approva_certificazione") 
	 */
	public function approvaCertificazioneAction($id_certificazione) {
        $this->get('base')->checkCsrf('token');
        
		$certificazione = $this->getEm()->getRepository("CertificazioniBundle\Entity\Certificazione")->find($id_certificazione); 
        
        $em = $this->getEm();
        
		if (!$certificazione->isApprovabile()) {
            $this->addFlash("error", "Azione non compatile con lo stato della certificazione");
		}
        
		try {
            $em->beginTransaction(); 
            $this->container->get("sfinge.stati")->avanzaStato($certificazione, StatoCertificazione::CERT_APPROVATA);
			$em->flush();
            foreach ($certificazione->getPagamenti() as $certificazione_pagamento) {
                $pagamento = $certificazione_pagamento->getPagamento();
                if ($certificazione_pagamento->getImporto() > 0) {
                    $pagamento->setImportoCertificato($pagamento->getImportoCertificato() + $certificazione_pagamento->getImporto() - $certificazione_pagamento->getImportoTaglio());
                } else {
					// errore ??? Che succede in caso di più de-certificazioni per un pagamento?
					$pagamento->setImportoDecertificato($pagamento->getImportoDecertificato() + abs($certificazione_pagamento->getImporto()));
                    // $pagamento->setImportoDecertificato(abs($certificazione_pagamento->getImporto()));
                }
            } 
            $certificazione->setDataApprovazione(new \DateTime());   
            // MONITORAGGIO: popolamento della destinazione RichiestaSpesaCertificata.
			// commento perchè la funzione non esiste almeno fino ad oggi 09/02/2018
            //$this->popolaRichiestaSpesaCertificata($certificazione);
            
            $em->flush();
            $em->commit();
            $this->addFlash("success", "La certificazione è stata correttamente approvata");         
		} catch (ResponseException $e) {
            $em->rollback();
            $this->addFlash("error", "Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza");		
        } 
        
		return $this->redirectToRoute("elenco_certificazioni");
	}     

    
}
