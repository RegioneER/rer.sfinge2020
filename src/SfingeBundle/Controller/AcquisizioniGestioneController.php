<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Form\Type\DocumentoFileType;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use SfingeBundle\Entity\Acquisizioni;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\DocumentoProcedura;
use SfingeBundle\Entity\ManifestazioneInteresse;
use SfingeBundle\Form\Entity\RicercaProcedura;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use DocumentoBundle\Component\ResponseException;

/**
 * 
 */
class AcquisizioniGestioneController extends BaseController {

	/**
	 * @Route("/crea_acquisizione", name="crea_acquisizione")
	 * @Template("SfingeBundle:Procedura:acquisizione.html.twig")
	 * @Menuitem(menuAttivo = "creaTrasporti")
	 * @PaginaInfo(titolo="Nuova procedura acquisizione",sottoTitolo="pagina per creare una nuova procedura acquisizione")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="Crea procedura trasporti")})
	 */
	public function creaAcquisizioneAction() {
		$em = $this->getDoctrine()->getManager();

		$acquisizione = new Acquisizioni();
		$request = $this->getCurrentRequest();

		$assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());
		$modalita_pagamento = $em->getRepository("AttuazioneControlloBundle\Entity\ModalitaPagamento")->findAll();

		if (count($assi) == 0) {
			$this->addFlash('error', "Nessun asse associato");
			return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
		}
		
		$stato = $this->getEm()->getRepository("SfingeBundle:StatoProcedura")->findOneByCodice("CONCLUSO");
		$acquisizione->setStatoProcedura($stato);
		$acquisizione->setNumeroRichieste(1);
		
		$options["assi"] = $assi;
		$options["readonly"] = false;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");

		$form = $this->createForm('SfingeBundle\Form\AcquisizioniType', $acquisizione, $options);

		if ($request->isMethod('POST')) {

			$form->bind($request);

			$acquisizione->setTipiOperazioni(array($acquisizione->getTipiOperazioni()));

			if ($form->isValid()) {
				try {
					foreach ($modalita_pagamento as $modalita) {
						$modalita_pagamento_procedura = new \AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura();
						$modalita_pagamento_procedura->setModalitaPagamento($modalita);
						$modalita_pagamento_procedura->setProcedura($acquisizione);
                        $acquisizione->addModalitaPagamento($modalita_pagamento_procedura);
						$em->persist($modalita_pagamento_procedura);
					}
                    
					$acquisizione->setModalitaFinanziamentoAttiva(false);
					$acquisizione->setVisibileInCorso(true);
					$acquisizione->setRendicontazioneAttiva(false);
					$em->persist($acquisizione);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}else{
				$this->addFlash('error', 'Alcuni valori non sono validi');
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["acquisizione"] = $acquisizione;
		$form_params["lettura"] = false;
		return $form_params;
	}

	/**
	 * @Route("/acquisizioni_modifica/{id_acquisizione}", name="acquisizioni_modifica")
	 * @Template("SfingeBundle:Procedura:acquisizione.html.twig")
	 * @PaginaInfo(titolo="Modifica procedura acquisizione",sottoTitolo="pagina per modificare i dati della procedura acquisizione selezionata")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco Atti Amministrativi", route="elenco_atti_amministrativi"), @ElementoBreadcrumb(testo="modifica procedura acquisizione")})
	 */
	public function modificaAcquisizioneAction($id_acquisizione) {
		$em = $this->getDoctrine()->getManager();
		$acquisizione = $em->getRepository('SfingeBundle:Procedura')->findOneById($id_acquisizione);
		if (\is_null($acquisizione)) {
			$this->addFlash('error', "Procedura trasporti non trovata");
			return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
		}

		$request = $this->getCurrentRequest();
		
		$assi = $em->getRepository("SfingeBundle\Entity\Asse")->getAssi($this->getUser());

		if (count($assi) == 0) {
			$this->addFlash('error', "Nessun asse associato");
			return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
		}

		$options["assi"] = $assi;

		$options["readonly"] = false;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $this->generateUrl("elenco_atti_amministrativi");
        
        $tipi_operazioni = $acquisizione->getTipiOperazioni()->toArray();
        $acquisizione->setTipiOperazioni($tipi_operazioni[0]);

		$form = $this->createForm('SfingeBundle\Form\AcquisizioniType', $acquisizione, $options);

		if ($request->isMethod('POST')) {

			$form->bind($request);
            
            $acquisizione->setTipiOperazioni(array($acquisizione->getTipiOperazioni()));

			if ($form->isValid()) {
				try {
					$em->persist($acquisizione);
					$em->flush();
					$this->addFlash('success', "Modifiche salvate correttamente");

					return $this->redirect($this->generateUrl('elenco_atti_amministrativi'));
				} catch (ResponseException $e) {
					$this->addFlash('error', $e->getMessage());
				}
			}else{
				$this->addFlash('error', 'Alcuni valori non sono validi');
			}
		}

		$form_params["form"] = $form->createView();
		$form_params["acquisizione"] = $acquisizione;
		$form_params["lettura"] = true;

		return $form_params;
	}

}
