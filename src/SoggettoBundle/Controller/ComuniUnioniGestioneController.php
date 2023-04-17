<?php

namespace SoggettoBundle\Controller;

use AnagraficheBundle\Entity\Persona;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SoggettoBundle\Entity\Adrier;
use SoggettoBundle\Entity\AdrierPersona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\TipoIncarico;
use SoggettoBundle\Entity\StatoIncarico;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use SoggettoBundle\Form\Entity\NuovoComune;
use SoggettoBundle\Form\NuovoComuneType;
use Symfony\Component\Form\FormError;
use BaseBundle\Annotation\ControlloAccesso;
use GeoBundle\Entity\GeoStato;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ComuniUnioniGestioneController extends SoggettoBaseController {

	/**
	 * @Route("/crea_comune_unione", name="crea_comune_unione")
	 * @Template("SoggettoBundle:Soggetto:comuneUnione.html.twig")
	 * @Menuitem(menuAttivo = "creaSoggettoGiuridico")
	 * @PaginaInfo(titolo="Nuovo Comune o Unione di comuni",sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco comuni o unioni", route="elenco_comuni_unioni"), @ElementoBreadcrumb(testo="Crea comune o unione di comuni")})
	 */
	public function creaComuneUnioneAction(Request $request): Response {
        $em = $this->getDoctrine()->getManager();

        $formModel = new NuovoComune();
        /** @var GeoStato $italia */
        $italia = $em->getRepository(GeoStato::class)->findOneBy([
            'codice' => GeoStato::COD_ITALIA,
        ]);
        $formModel->comune->setStato($italia);

        if($request->query->has('codice_fiscale')) {
            $codiceFiscaleComune = $request->query->get('codice_fiscale');
            $formModel->comune->setCodiceFiscale($codiceFiscaleComune);

            /** @var Adrier $adrier */
            $adrier = $this->container->get('app.adrier_service')->dettaglioAdrier($codiceFiscaleComune, 'DettaglioCompletoImpresa');
            if (! is_null($adrier) && 'OK' === $adrier->getHeader()->getEsito()) {
                $datiImpresa = $adrier->getDati()->getDatiImpresa();
                $formModel->comune->setCodiceFiscale($datiImpresa->getEstremiImpresa()->getCodiceFiscale());
                $formModel->comune->setDenominazione($datiImpresa->getEstremiImpresa()->getDenominazione());
                $formModel->comune->setPartitaIva($datiImpresa->getEstremiImpresa()->getPartitaIva());
                $formModel->comune->setCodiceAteco($this->getCodiceAtecoDaAdrier($datiImpresa->getInformazioniSede()->getAtecoPrincipale()));
                $formModel->comune->setCodiceAtecoSecondario($this->getCodiceAtecoDaAdrier($datiImpresa->getInformazioniSede()->getAtecoSecondario()));

                $formModel->comune->setFormaGiuridica($this->getFormaGiuridicaDaAdrier($datiImpresa->getEstremiImpresa()->getFormaGiuridica()->getDescrizione()));

                $formModel->comune->setVia($datiImpresa->getInformazioniSede()->getIndirizzo()->getVia());
                $formModel->comune->setCivico($datiImpresa->getInformazioniSede()->getIndirizzo()->getNCivico());

                // da verificare perchè non ho trovato aziende con sede legale all'estero per poter fare un test
                $formModel->comune->setStato($this->getStatoSedeDaAdrier($datiImpresa->getInformazioniSede()->getIndirizzo()->getCComune()));

                $formModel->comune->setComune($this->getComuneSedeDaAdrier($datiImpresa->getInformazioniSede()->getIndirizzo()->getCComune()));
                $formModel->comune->setCap($datiImpresa->getInformazioniSede()->getIndirizzo()->getCap());
                $formModel->comune->setLocalita(implode(" - ", [$datiImpresa->getInformazioniSede()->getIndirizzo()->getFrazione(), $datiImpresa->getInformazioniSede()->getIndirizzo()->getAltreIndicazioni()]));
                $formModel->comune->setTel($datiImpresa->getInformazioniSede()->getIndirizzo()->getTelefono());
                $formModel->comune->setFax($datiImpresa->getInformazioniSede()->getIndirizzo()->getFax());
                $formModel->comune->setEmailPec($datiImpresa->getInformazioniSede()->getIndirizzo()->getIndirizzoPec());

                if($datiImpresa->getLegaleRappresentante() instanceof AdrierPersona) {
                    $formModel->legaleRappresentante->setNome($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getNome());
                    $formModel->legaleRappresentante->setCognome($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getCognome());
                    $formModel->legaleRappresentante->setSesso($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getSesso());

                    $formModel->legaleRappresentante->setCodiceFiscale($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getCodiceFiscale());
                    $formModel->legaleRappresentante->setNazionalita($this->getStatoLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getStato(), $formModel->legaleRappresentante->getCodiceFiscale()));
                    $formModel->legaleRappresentante->setDataNascita($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getData());
                    $formModel->legaleRappresentante->setStatoNascita($this->getStatoLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getStato(), $formModel->legaleRappresentante->getCodiceFiscale()));
                    $formModel->legaleRappresentante->setComune($this->getComuneLrDaAdrier($datiImpresa->getLegaleRappresentante()->getPersonaFisica()->getEstremiNascita()->getComune(), $formModel->legaleRappresentante->getCodiceFiscale()));
                }
            }
            $codiceFiscaleLegaleRappresentante = $formModel->legaleRappresentante->getCodiceFiscale();
            if ($codiceFiscaleLegaleRappresentante) {
                // Collego persona se già presente a sistema
                $personaDB = $em->getRepository(Persona::class)->findOneBy([
                    'codice_fiscale' => $codiceFiscaleLegaleRappresentante,
                ]);
                if ($personaDB) {
                    $formModel->legaleRappresentante = $personaDB;
                }
            }
        }

        $options["url_indietro"] = $request->headers->get('referer');
        $options["tipo"] = $request->query->get('tipo');

        $form = $this->createForm(NuovoComuneType::class, $formModel, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            // Controlli post submit
            $formAzienda = $form->get('comune');
            $piva_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('piva', $formAzienda->get('partita_iva')->getData());
            if ($piva_presente) {
                $formAzienda->get('partita_iva')->addError(new FormError('Partita IVA già presente a sistema'));
            }
            $cf_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('cf', $formAzienda->get('codice_fiscale')->getData());
            if ($cf_presente) {
                $formAzienda->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
            }

            if ($form->isValid()) {
                $codice_organismo = $em->getRepository("SoggettoBundle:Soggetto")->getMaxCodiceOrganismo() + 1;
                $formModel->comune->setCodiceOrganismo($codice_organismo);
                $formModel->comune->setDenominazione($formModel->comune->getComuneUnioneComune()->getDescrizione());
                try {
                    $legaleRappresentanteDaDB = $em->getRepository(Persona::class)->findOneBy([
                        'codice_fiscale' => $formModel->legaleRappresentante->getCodiceFiscale(),
                    ]);
                    if($legaleRappresentanteDaDB){
                        $legaleRappresentanteDaDB->mergeData($formModel->legaleRappresentante);
                        $formModel->legaleRappresentante = $legaleRappresentanteDaDB;
                    }

                    $tipoIncaricoLegaleRappresentante = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::LR);
                    $statoAttivo = $this->trovaDaCostante("SoggettoBundle:StatoIncarico", StatoIncarico::ATTIVO);
                    $incaricoLegaleRappresentante = $formModel->comune->incarica($formModel->legaleRappresentante, $tipoIncaricoLegaleRappresentante, $statoAttivo);
                    $em->persist($formModel->comune);
                    $em->persist($formModel->legaleRappresentante);
                    $em->persist($incaricoLegaleRappresentante);

                    $tipoIncaricoUtentePrincipale = $this->trovaDaCostante("SoggettoBundle:TipoIncarico", TipoIncarico::UTENTE_PRINCIPALE);
                    $incaricoUtentePrincipale = $formModel->comune->incarica($this->getPersona(), $tipoIncaricoUtentePrincipale, $statoAttivo);
                    $em->persist($incaricoUtentePrincipale);

                    $this->aggiungiSedeLegaleAlleSedi($formModel->comune, $em);

                    $em->flush();

                    $this->addFlash('success', "Modifiche salvate correttamente");
                    return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
                } catch (Exception $e) {
                    $this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
                    $this->get("logger")->error($e->getMessage());
                }
            }
        }

        $form_params = ["form" => $form->createView()];
        return $this->render('@Soggetto/Soggetto/nuovoComune.html.twig', $form_params);
	}

	/**
	 * @Route("/comune_unione_modifica/{id_soggetto}", name="comune_unione_modifica")
	 * @Template("SoggettoBundle:Soggetto:comuneUnione.html.twig")
	 * @Menuitem(menuAttivo = "elencoSoggettiGiuridici")
	 * @PaginaInfo(titolo="Modifica Comune o Unione di comuni",sottoTitolo="")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Elenco comuni o unioni", route="elenco_comuni_unioni"), @ElementoBreadcrumb(testo="Modifica comune o unione di comuni")})
	 * @ControlloAccesso(contesto="soggetto", classe="SoggettoBundle:Soggetto", opzioni={"id" = "id_soggetto"}, azione="edit")
	 */
	public function modificaComuneUnioneAction(Request $request, $id_soggetto) {
		$em = $this->getDoctrine()->getManager();
		$funzioniService = $this->get('funzioni_utili');

		/** @var Soggetto $azienda */
		$azienda = $em->getRepository('SoggettoBundle:ComuneUnione')->findOneById($id_soggetto);

		$data = $funzioniService->getDataComuniFromRequestSedeLegale($request, $azienda);

		$options["readonly"] = false;
		$options["dataIndirizzo"] = $data;
		$options["em"] = $this->getEm();
		$options["url_indietro"] = $request->headers->get('referer'); // $this->generateUrl("elenco_comuni_unioni");
        $options["tipo"] = $azienda->getTipoByFormaGiuridica();

		$form = $this->createForm('SoggettoBundle\Form\ComuneUnioneType', $azienda, $options);

		if ($request->isMethod('POST')) {
			$form->bind($request);

            if($form->isSubmitted() && !is_null($azienda->getComuneUnioneComune())) {
                $azienda->setDenominazione($azienda->getComuneUnioneComune()->getDescrizione());
            }

			if ($form->isValid()) {
				$piva_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('piva', $form['partita_iva']->getData(), $azienda);
				$cf_presente = $em->getRepository("SoggettoBundle:Soggetto")->isCodicePresente('cf', $form['codice_fiscale']->getData(), $azienda);
				//TODO mettere controllo che lo stesso comune non possa essere registrato piu volta
				if ($piva_presente) {
					$form->get('partita_iva')->addError(new FormError('Partita IVA già presente a sistema'));
				} else if ($cf_presente) {
					$form->get('codice_fiscale')->addError(new FormError('Codice fiscale già presente a sistema'));
				} else {
					try {
						$em->persist($azienda);
						$em->flush();
						$this->addFlash('success', "Modifiche salvate correttamente");

						return $this->redirect($this->generateUrl('elenco_soggetti_giuridici'));
					} catch (Exception $e) {
						$this->addFlash('error', "Si è verificato un errore nel salvataggio dei dati. Si prega di contattare l'assistenza tecnica");
						$this->get("logger")->error($e->getMessage());
					}
				}
			}
		} else {
            if(!$azienda->isFormaGiuridicaCoerente()) {
                $this->addFlash('warning', 'Attenzione! La forma giuridica indicata potrebbe non essere corretta.');
            }
        }

		$form_params["form"] = $form->createView();
		$form_params["azienda"] = $azienda;

		return $form_params;
	}

}
