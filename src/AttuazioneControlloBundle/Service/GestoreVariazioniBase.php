<?php

namespace AttuazioneControlloBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use AttuazioneControlloBundle\Entity\StatoVariazione;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Form\Entity\TipoVariazione;
use BaseBundle\Exception\SfingeException;

class GestoreVariazioniBase extends AGestoreVariazioni {
    public function elencoVariazioni($richiesta): Response {
        return $this->render("AttuazioneControlloBundle:Variazioni:elencoVariazioni.html.twig", ["richiesta" => $richiesta]);
    }

    public function aggiungiVariazione(Richiesta $richiesta): Response {
        $em = $this->getEm();
        $options = [
            "url_indietro" => $this->generateUrl("elenco_variazioni", ["id_richiesta" => $richiesta->getId()]),
            "firmatabili" => $em->getRepository("SoggettoBundle:Soggetto")->getFirmatariAmmissibili($richiesta->getSoggetto()),
        ];
        $atc = $richiesta->getAttuazioneControllo();
        $tipoVariazione = new TipoVariazione($atc);
        $form = $this->createForm(\AttuazioneControlloBundle\Form\TipoVariazioneType::class, $tipoVariazione, $options);
        $request = $this->getCurrentRequest();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->checkAggiungiVariazione($tipoVariazione);
                $variazione = $tipoVariazione->getIstanzaVariazione();
                $atc->addVariazioni($variazione);
                $em->beginTransaction();
                $em->persist($variazione);
                $this->container->get("sfinge.stati")->avanzaStato($variazione, StatoVariazione::VAR_INSERITA);
                $em->flush();
                $em->commit();
                return $this->addSuccesRedirect("La variazione è stata correttamente aggiunta", "elenco_variazioni", ["id_richiesta" => $richiesta->getId()]);
            } catch (SfingeException $e) {
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                $em->rollback();
                $this->container->get('logger')->error($e->getTraceAsString());
                $this->addError("Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l'assistenza.");
                throw $e;
            }
        }

        $dati = [
            "form" => $form->createView(),
        ];

        return $this->render("AttuazioneControlloBundle:Variazioni:aggiungiVariazione.html.twig", $dati);
    }

    /**
     * @throws SfingeException
     */
    protected function checkAggiungiVariazione(TipoVariazione $tipoVariazione): void {
    }
}
