<?php

namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Form\Entity\PrioritaStrategiaProponente;
use RichiesteBundle\Entity\PrioritaProponente;
use RichiesteBundle\Entity\Richiesta;

class GestorePrioritaBase extends AGestorePriorita {
    public function gestionePriorita($id_richiesta, $opzioni = []) {
        $richiesta = $this->getRichiesta($id_richiesta);
        $gestoreRichiesta = $this->getGestoreRichieste($richiesta);

        $hasSezionePriorita = $gestoreRichiesta->hasSezionePriorita();

        if (!$hasSezionePriorita) {
            throw new SfingeException("Gestione priorità non prevista");
        }

        $isPrioritaRichiesta = $gestoreRichiesta->isPrioritaRichiesta();
        $hasSistemiProduttiviMultipli = $gestoreRichiesta->hasSistemiProduttiviMultipli();

        if ($isPrioritaRichiesta && !$hasSistemiProduttiviMultipli) {
            return $this->gestionePrioritaRichiestaSingoloSistema($id_richiesta, $opzioni);
        } else {
            throw new SfingeException();
        }
    }

    public function gestionePrioritaRichiestaSingoloSistema($id_richiesta, $opzioni = []) {
        $richiesta = $this->getRichiesta($id_richiesta);
        $gestore_richieste = $this->getGestoreRichieste($richiesta);
        $request = $this->getCurrentRequest();

        $twig = "RichiesteBundle:Richieste:gestionePrioritaRichiestaSingoloSistema.html.twig";
        if (isset($opzioni['twig'])) {
            $twig = $opzioni['twig'];
            unset($opzioni['twig']);
        }

        $opzioni["disabled"] = $gestore_richieste->isRichiestaDisabilitata() || !$this->isGranted("ROLE_UTENTE");
        $opzioni["url_indietro"] = $this->generateUrl("dettaglio_richiesta", ["id_richiesta" => $richiesta->getId()]);
        $opzioni["request_data"] = $request->request;
        $proponenteMandatario = $richiesta->getMandatario();

        $prioritaStrategiaProponente = new PrioritaStrategiaProponente();
        $prioritaStrategiaProponente->setDrivers($proponenteMandatario->getDrivers());
        $prioritaStrategiaProponente->setKets($proponenteMandatario->getKets());

        if ($proponenteMandatario->getPriorita()->count() > 0) {
            $prioritaPre = $proponenteMandatario->getPriorita()->first();
            $prioritaStrategiaProponente->setSistemaProduttivo($prioritaPre->getSistemaProduttivo());
            $prioritaStrategiaProponente->setOrientamentoTematico($prioritaPre->getOrientamentoTematico());
            $prioritaStrategiaProponente->setPrioritaTecnologiche($prioritaPre->getPrioritaTecnologiche());

            if (isset($opzioni['coerenza']) && 1 == $opzioni['coerenza']) {
                $prioritaStrategiaProponente->setCoerenzaObiettivi($prioritaPre->getCoerenzaObiettivi());
            }
        }

        $form = $this->createForm("RichiesteBundle\Form\PrioritaStrategiaProponenteType", $prioritaStrategiaProponente, $opzioni);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if (0 == $proponenteMandatario->getPriorita()->count()) {
                $proponenteMandatario->addPriorita(new PrioritaProponente());
            }
            if (!$request->query->has("no-flush") && $form->isValid()) {
                $em = $this->getEm();
                try {
                    $priorita = $proponenteMandatario->getPriorita()->first();
                    $priorita->setSistemaProduttivo($prioritaStrategiaProponente->getSistemaProduttivo());
                    $priorita->setOrientamentoTematico($prioritaStrategiaProponente->getOrientamentoTematico());
                    if (1 == $opzioni['has_priorita_tecnologiche']) {
                        $priorita->setPrioritaTecnologiche($prioritaStrategiaProponente->getPrioritaTecnologiche());
                    }
                    if (isset($opzioni['coerenza']) && 1 == $opzioni['coerenza']) {
                        $priorita->setCoerenzaObiettivi($prioritaStrategiaProponente->getCoerenzaObiettivi());
                    }
                    $proponenteMandatario->setDrivers($prioritaStrategiaProponente->getDrivers());
                    $proponenteMandatario->setKets($prioritaStrategiaProponente->getKets());
                    $em->flush();

                    return new GestoreResponse($this->addSuccesRedirect("Dati priorità salvati correttamente", "dettaglio_richiesta", ["id_richiesta" => $richiesta->getId()]));
                } catch (\Exception $e) {
                    throw new SfingeException("Dati priorità non salvati. " . $e);
                }
            }
        }

        $dati = ["id_richiesta" => $richiesta->getId(), "form" => $form->createView(), "has_driver_kets" => $opzioni['has_driver_kets'], "has_priorita_tecnologiche" => $opzioni['has_priorita_tecnologiche']];

        $response = $this->render($twig, $dati);
        return new GestoreResponse($response);
    }

    public function validaPriorita($id_richiesta): EsitoValidazione {
        $richiesta = $this->getRichiesta($id_richiesta);
        $procedura = $richiesta->getProcedura();
        $gestoreRichieste = $this->getGestoreRichieste($richiesta);
        $hasSezionePriorita = $gestoreRichieste->hasSezionePriorita();

        if (!$hasSezionePriorita) {
            throw new SfingeException("Gestione priorità non prevista");
        }

        $isPrioritaRichiesta = $gestoreRichieste->isPrioritaRichiesta();
        $hasSistemiProduttiviMultipli = $gestoreRichieste->hasSistemiProduttiviMultipli();

        if ($isPrioritaRichiesta && !$hasSistemiProduttiviMultipli) {
            return $this->validaPrioritaRichiestaSingoloSistema($id_richiesta);
        } else {
            throw new SfingeException();
        }
    }

    public function validaPrioritaRichiestaSingoloSistema($id_richiesta): EsitoValidazione {
        $richiesta = $this->getRichiesta($id_richiesta);
        $proponenteMandatario = $richiesta->getMandatario();
        $priorita = $proponenteMandatario->getPriorita();

        $esito = new EsitoValidazione($priorita->count() > 0);

        return $esito;
    }

    protected function getRichiesta($id_richiesta): Richiesta {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (\is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $richiesta;
    }

    protected function getGestoreRichieste(Richiesta $richiesta): IGestoreRichiesta {
        $procedura = $richiesta->getProcedura();
        $gestore = $this->container->get("gestore_richieste")->getGestore($procedura);

        return $gestore;
    }
}
