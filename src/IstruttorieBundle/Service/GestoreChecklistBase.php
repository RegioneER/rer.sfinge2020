<?php

namespace IstruttorieBundle\Service;

use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Service\GestoreResponse;
use Doctrine\Common\Collections\ArrayCollection;
use IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria;
use IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria;
use IstruttorieBundle\Entity\ElementoChecklistIstruttoria;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use RichiesteBundle\Entity\Proponente;
use IstruttorieBundle\Entity\ChecklistIstruttoria;
use Doctrine\Common\Collections\Collection;

class GestoreChecklistBase extends AGestoreChecklist {

    public function getIstruttoriaRichiesta($id_richiesta) {
        return $this->getEm()->getRepository("RichiesteBundle:Richiesta")->find($id_richiesta)->getIstruttoria();
    }

    protected function generaSingolaChecklist($istruttoria, $checklist, $proponente = null) {
        $valutazione = $this->getEm()->getRepository("IstruttorieBundle:ValutazioneChecklistIstruttoria")->findOneBy(array('istruttoria' => $istruttoria, 'checklist' => $checklist));
        if (is_null($valutazione)) {
            $valutazione = new ValutazioneChecklistIstruttoria();
            $valutazione->setValidata(false);
            $valutazione->setChecklist($checklist);

            if (!is_null($proponente)) {
                $valutazione->setProponente($proponente);
            }

            $istruttoria->addValutazioneChecklist($valutazione);
        }
        $elementi_da_escludere = $this->getElementiDaEscludere($istruttoria, $checklist, $proponente);
        $sezioniDaEscludere = $this->getSezioniDaEscludere($istruttoria, $checklist, $proponente);
        $sezioni = $checklist->getSezioni()->filter(function ($entry) use ($sezioniDaEscludere) {
            $codice = $entry->getCodice();
            if (\is_null($codice)) {
                return true;
            }
            return !$sezioniDaEscludere->contains($entry);
        });
        foreach ($sezioni as $sezione) {
            foreach ($sezione->getElementi() as $elemento) {
                if (in_array($elemento->getCodice(), $elementi_da_escludere)) {
                    continue;
                }

                $valutazione_elemento = $this->getEm()->getRepository("IstruttorieBundle:ValutazioneElementoChecklistIstruttoria")->findOneBy(array('elemento' => $elemento, 'valutazione_checklist' => $valutazione));
                if (!is_null($valutazione_elemento)) {
                    continue;
                }
                $valutazione_elemento = new \IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria();
                $valutazione_elemento->setElemento($elemento);
                $valutazione->addValutazioneElemento($valutazione_elemento);
            }
        }
    }

    public function genera($istruttoria, $checklist, $proponente = null) {
        if ($checklist->getProponente()) {
            foreach ($istruttoria->getRichiesta()->getProponenti() as $proponente) {
                $this->generaSingolaChecklist($istruttoria, $checklist, $proponente);
            }
        } else {
            $this->generaSingolaChecklist($istruttoria, $checklist);
        }
    }

    public function valuta($valutazione_checklist, $extra = array()) {
        $istruttoria = $valutazione_checklist->getIstruttoria();
        $richiesta = $istruttoria->getRichiesta();
        $options = array();
        $options["url_indietro"] = $this->generateUrl('elenco_richieste_inviate');
        $options["action"] = $this->generateUrl("valuta_checklist_istruttoria", array("id_valutazione_checklist" => $valutazione_checklist->getId()));
        // rilassato vincolo..si può validare anche se c'è una richiesta di integrazione
        $options["disabled"] = !$this->isGranted($valutazione_checklist->getChecklist()->getRuolo()) || $valutazione_checklist->getValidata(); // || $istruttoria->getSospesa();
        $options["invalida"] = $valutazione_checklist->getValidata(); // && is_null($valutazione_checklist->getIstruttoria()->getEsito());
        $options["integrazione"] = $this->isGranted($valutazione_checklist->getChecklist()->getRuolo()) && !$istruttoria->getSospesa() && is_null($valutazione_checklist->getIstruttoria()->getEsito());
        $options["url_integrazione"] = $this->generateUrl('crea_integrazione_istruttoria', array("id_valutazione_checklist" => $valutazione_checklist->getId()));

        $form = $this->createForm("IstruttorieBundle\Form\ValutazioneChecklistIstruttoriaType", $valutazione_checklist, $options);

        $request = $this->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            if ($form->get("pulsanti")->get("pulsante_valida")->isClicked()) {
                if (!$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE")) {
                    throw new \Exception("Operazione non ammessa per l'utente");
                }

                $validazione = $this->valida($valutazione_checklist);

                if (!$validazione->getEsito()) {
                    $form->addError(new \Symfony\Component\Form\FormError("Dati non completi o non corretti"));
                }
            }

            if ($form->isValid()) {
                $request_data = $request->request->get($form->getName());

                foreach ($form->get("valutazioni_elementi")->getIterator() as $child) {
                    $valutazione_elemento = $child->getData();
                    $elemento = $valutazione_elemento->getElemento();

                    switch ($elemento->getTipo()) {
                        case "choice":
                            $choices = $elemento->getChoices();
                            $valutazione_elemento->setValoreRaw(is_null($valutazione_elemento->getValore()) ? null : $choices[$valutazione_elemento->getValore()]);
                            break;
                        case "date":
                            $valore = $valutazione_elemento->getValore();
                            if (!is_null($valore)) {
                                $valutazione_elemento->setValore($valore->format('Y-m-d'));
                                $valutazione_elemento->setValoreRaw($valore->format('d/m/Y'));
                            }
                            break;
                        case "datetime":
                            $valore = $valutazione_elemento->getValore();
                            if (!is_null($valore)) {
                                $valutazione_elemento->setValoreRaw($valore->format('d/m/Y H:i'));
                                $valutazione_elemento->setValore($valore->format('Y-m-d H:i'));
                            }
                            break;
                        default:
                            $valutazione_elemento->setValoreRaw($valutazione_elemento->getValore());
                    }
                }

                if ($form->get("pulsanti")->get("pulsante_valida")->isClicked()) {
                    $valutazione_checklist->setValidata(true);
                    $valutazione_checklist->setValutatore($this->getUser());
                    $valutazione_checklist->setDataValidazione(new \DateTime());
                    $valutazione_checklist->setAmmissibile($this->isAmmissibile($valutazione_checklist));
                    $this->operazioniValidazione($valutazione_checklist);

                    $messaggio = "Valutazione validata";
                    $redirect_url = $this->generateUrl('valuta_checklist_istruttoria', array('id_valutazione_checklist' => $valutazione_checklist->getId()));
                } else {
                    if (isset($request_data["pulsanti"]["pulsante_invalida"])) {
                        if (!$this->isGranted("ROLE_ISTRUTTORE_SUPERVISORE")) {
                            throw new \Exception("Operazione non ammessa per l'utente");
                        }
                        $valutazione_checklist->setValidata(false);
                        $valutazione_checklist->setValutatore(null);
                        $valutazione_checklist->setDataValidazione(null);
                        $valutazione_checklist->setAmmissibile(null);

                        $messaggio = "Valutazione invalidata";
                        $redirect_url = $this->generateUrl('valuta_checklist_istruttoria', array('id_valutazione_checklist' => $valutazione_checklist->getId()));
                    } else {
                        $messaggio = "Modifiche salvate correttamente";
                        $redirect_url = $this->generateUrl('riepilogo_richiesta', array("id_richiesta" => $valutazione_checklist->getIstruttoria()->getRichiesta()->getId()));
                    }
                }

                $em = $this->getEm();
                $gestore_istruttoria = $this->container->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
                $gestore_istruttoria->creaLogIstruttoria($istruttoria, $valutazione_checklist->getChecklist()->getCodice());
                try {

                    $this->callbackBeforeFlush($valutazione_checklist);

                    $em->flush();

                    $this->addFlash('success', $messaggio);

                    return new GestoreResponse($this->redirect($redirect_url));
                } catch (\Exception $e) {
                    $this->addFlash('error', "Errore nel salvataggio delle informazioni");
                }
            }
        }

        $dati["form"] = $form->createView();
        $dati["istruttoria"] = $istruttoria;
        $dati["richiesta"] = $istruttoria->getRichiesta();
        $dati["valutazione_checklist"] = $valutazione_checklist;

        if (isset($extra["twig_data"])) {
            $dati = array_merge($dati, $extra["twig_data"]);
        }

        //Aggiungo i parametri per bando in caso di cose trubole....deve essere un array
        if (isset($extra["parametri_bando"])) {
            $dati = array_merge($dati, $extra["parametri_bando"]);
        }

        //aggiungo il titolo della pagina e le info della breadcrumb
        $this->container->get("pagina")->setTitolo($valutazione_checklist->getChecklist()->getNome());
        // $this->container->get("pagina")->setSottoTitolo("pagina con la checklist della richiesta");

        $twig = isset($extra["twig"]) ? $extra["twig"] : "IstruttorieBundle:Istruttoria:checklistIstruttoria.html.twig";

        $response = $this->render($twig, $dati);

        return new GestoreResponse($response);
    }

    /**
     * @param ValutazioneChecklistIstruttoria $valutazione_checklist
     */
    public function valida($valutazione_checklist) {

        $esito = new EsitoValidazione(true);
        /** @var ValutazioneElementoChecklistIstruttoria $valutazione */
        foreach ($valutazione_checklist->getValutazioniElementi() as $valutazione) {
            $tipoElemento = $valutazione->getElemento();
            $valore = $valutazione->getValore();
            if (\is_null($valore) && !$valutazione->getElemento()->getOpzionale()) {
                $esito->setEsito(false);
                $esito->addMessaggio("La checklist non è completa");
            }
            if ($tipoElemento->getTipo() == ElementoChecklistIstruttoria::INTEGER && (
                (!\is_null($tipoElemento->getPunteggioMassimo()) && $valore > $tipoElemento->getPunteggioMassimo()) ||
                (!\is_null($tipoElemento->getPunteggioMinimo()) && $valore < $tipoElemento->getPunteggioMinimo())
                )) {
                $esito->setEsito(false);
                $esito->addMessaggio("Sono presenti valori non validi");
            }
        }

        return $esito;
    }

    public function isAmmissibile($valutazione_checklist) {
        throw new \Exception("Deve essere implementato nella classe derivata");
    }

    public function operazioniValidazione($valutazione_checklist) {
        $richiesta = $valutazione_checklist->getIstruttoria()->getRichiesta();
        $gestore_istruttoria = $this->container->get("gestore_istruttoria")->getGestore($richiesta->getProcedura());
        $gestore_istruttoria->aggiornaIstruttoriaRichiesta($richiesta->getId());
    }

    public function getElementiDaEscludere($istruttoria, $checklist, $proponente = null) {
        return array();
    }

    /**
     * Ritorna un array di stringhe contenti i codici da escludere
     */
    protected function getSezioniDaEscludere(IstruttoriaRichiesta $istruttoria, ChecklistIstruttoria $checklist, ?Proponente $proponente = null): Collection {
        return new ArrayCollection();
    }

    /**
     * da implementare dove serve nei gestori specifici
     */
    protected function callbackBeforeFlush(ValutazioneChecklistIstruttoria $valutazione_checklist): void {
        // do nothing
    }

}
