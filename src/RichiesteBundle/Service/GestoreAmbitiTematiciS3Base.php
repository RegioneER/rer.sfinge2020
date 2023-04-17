<?php
namespace RichiesteBundle\Service;

use BaseBundle\Exception\SfingeException;
use DocumentoBundle\Component\ResponseException;
use Exception;
use MonitoraggioBundle\Form\Entity\Regione;
use RichiesteBundle\Entity\AmbitoTematicoS3Proponente;
use RichiesteBundle\Entity\Bando127\OggettoSanificazione;
use RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente;
use RichiesteBundle\Entity\SedeOperativaRichiesta;
use RichiesteBundle\Form\Entity\ModificaAmbitoTematicoS3Proponente;
use RichiesteBundle\Utility\EsitoValidazione;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Response;

class GestoreAmbitiTematiciS3Base extends AGestoreAmbitiTematiciS3
{
    /**
     * @param Richiesta $richiesta
     * @param array $opzioni
     * @return Response|null
     */
    public function gestioneAmbitiTematiciS3(Richiesta $richiesta, array $opzioni = []): ?Response
    {
        $ambitiTematicoS3Multipli = $richiesta->getProcedura()->getAmbitiTematiciS3Multipli();
        $aggiungiAmbitoTematicoS3 = false;
        if (($ambitiTematicoS3Multipli || $richiesta->getMandatario()->getAmbitiTematiciS3()->count() == 0)
            && !$this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata()) {
            $aggiungiAmbitoTematicoS3 = true;
        }
        $dati = [
            'richiesta' => $richiesta,
            'ambiti_tematici_s3' => $richiesta->getMandatario()->getAmbitiTematiciS3(),
            'aggiungi_ambito_tematico_s3' => $aggiungiAmbitoTematicoS3,
            'disabled' => $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata(),
        ];
        return $this->render("RichiesteBundle:Richieste:dettaglioAmbitiTematiciS3.html.twig", $dati);
    }

    /**
     * @param int $id_richiesta
     * @return EsitoValidazione
     * @throws SfingeException
     */
    public function validaAmbitiTematiciS3(int $id_richiesta): EsitoValidazione
    {
        $richiesta = $this->getRichiesta($id_richiesta);
        $hasSezioneAmbitiTematiciS3 = $richiesta->getProcedura()->getSezioneAmbitiTematiciS3();
        if (!$hasSezioneAmbitiTematiciS3) {
            throw new SfingeException("Gestione ambiti prioritari S3 non prevista");
        }

        $proponenteMandatario = $richiesta->getMandatario();
        $ambitiTematiciS3 = $proponenteMandatario->getAmbitiTematiciS3();
        $esito = new EsitoValidazione(true);
        if ($ambitiTematiciS3->count() == 0) {
            $esito->setEsito(false);
            $esito->addMessaggioSezione("Indicare almeno un ambito prioritario S3");
        } else {
            foreach ($ambitiTematiciS3 as $ambitoTematicoS3) {
                if ($ambitoTematicoS3->getDescrittori()->count() == 0) {
                    $esito->setEsito(false);
                    $esito->addMessaggioSezione("Indicare almeno un descrittore per l’ambito tematico S3: " . $ambitoTematicoS3);
                }
            }
        }

        return $esito;
    }

    /**
     * @param Richiesta $richiesta
     * @return IGestoreRichiesta
     */
    protected function getGestoreRichieste(Richiesta $richiesta): IGestoreRichiesta
    {
        $procedura = $richiesta->getProcedura();
        return $this->container->get("gestore_richieste")->getGestore($procedura);
    }

    /**
     * @param $id_richiesta
     * @return Richiesta
     * @throws SfingeException
     */
    protected function getRichiesta($id_richiesta): Richiesta
    {
        $richiesta = $this->getEm()->getRepository('RichiesteBundle:Richiesta')->find($id_richiesta);
        if (is_null($richiesta)) {
            throw new SfingeException('Richiesta non trovata');
        }
        return $richiesta;
    }

    /**
     * @param Richiesta $richiesta
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function aggiungiAmbitoTematicoS3(Richiesta $richiesta, array $opzioni = []): GestoreResponse
    {
        $ambitoTematicoS3Proponente = new AmbitoTematicoS3Proponente();
        $ambitoTematicoS3Proponente->setProponente($richiesta->getMandatario());
        $options["url_indietro"] = $this->generateUrl("gestione_ambiti_tematici_s3", ["id_richiesta" => $richiesta->getId()]);
        $options["disabled"] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
        $request = $this->getCurrentRequest();
        $form = $this->createForm('RichiesteBundle\Form\AmbitoTematicoS3ProponenteType', $ambitoTematicoS3Proponente, $options);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $ambitoGiaPresente = $richiesta->getMandatario()->getAmbitiTematiciS3()->filter(function (AmbitoTematicoS3Proponente $ambito) use ($ambitoTematicoS3Proponente) {
                        return $ambito->getAmbitoTematicoS3() === $ambitoTematicoS3Proponente->getAmbitoTematicoS3();
                    });

                    if ($ambitoGiaPresente->count()) {
                        $this->addFlash('error', 'Ambito Tematico S3 "' . $ambitoTematicoS3Proponente . '" già selezionato');
                    } else {
                        $em = $this->getEm();
                        $em->persist($ambitoTematicoS3Proponente);
                        $em->flush();
                        $this->addFlash('success', 'Dati salvati correttamente');
                    }

                    return new GestoreResponse($this->redirect(
                        $this->generateUrl('gestione_ambiti_tematici_s3', [
                            'id_richiesta' => $richiesta->getId()
                        ]))
                    );
                } catch (Exception $e) {
                    $this->addFlash('error', 'Inserimento dell’ambito tematico S3 non andato a buon fine');
                }
            }
        }

        $options = [];
        $options["form"] = $form->createView();
        $options["richiesta"] = $richiesta;
        $options["disabled"] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
        $twig = 'RichiesteBundle:Richieste:aggiungiAmbitoTematicoS3.html.twig';
        $response = $this->render($twig, $options);
        return new GestoreResponse($response, $twig, $options);
    }

    /**
     * @param int $id_ambito_tematico_s3_proponente
     * @return GestoreResponse|void
     */
    public function eliminaAmbitoTematicoS3Proponente(int $id_ambito_tematico_s3_proponente)
    {
        $em = $this->getEm();
        $ambitoTematicoS3Proponente = $em->getRepository("RichiesteBundle\Entity\AmbitoTematicoS3Proponente")
            ->find($id_ambito_tematico_s3_proponente);
        $richiesta = $ambitoTematicoS3Proponente->getProponente()->getRichiesta();
        try {
            foreach ($ambitoTematicoS3Proponente->getDescrittori() as $descrittore) {
                $em->remove($descrittore);
            }
            $em->flush();

            $em->remove($ambitoTematicoS3Proponente);
            $em->flush();
            return new GestoreResponse($this->addSuccesRedirect("Ambito Tematico S3 eliminato correttamente",
                'gestione_ambiti_tematici_s3', ['id_richiesta' => $richiesta->getId()]));
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @param AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente
     * @param array $opzioni
     * @return Response|null
     */
    public function gestioneDescrittori(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente, array $opzioni = []): ?Response
    {
        $dati = [
            'richiesta' => $ambitoTematicoS3Proponente->getProponente()->getRichiesta(),
            'ambito_tematico_s3_proponente' => $ambitoTematicoS3Proponente,
            'disabled' => $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata(),
        ];
        return $this->render("RichiesteBundle:Richieste:elencoDescrittoriAmbitiTematiciS3.html.twig", $dati);
    }

    /**
     * @param AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente
     * @param array $opzioni
     * @return GestoreResponse
     */
    public function aggiungiDescrittoreAmbitoTematicoS3(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente, array $opzioni = []): GestoreResponse
    {
        $richiesta = $ambitoTematicoS3Proponente->getProponente()->getRichiesta();
        $descrittoreAmbitoTematicoS3Proponente = new DescrittoreAmbitoTematicoS3Proponente();
        $descrittoreAmbitoTematicoS3Proponente->setAmbitoTematicoS3Proponente($ambitoTematicoS3Proponente);
        $options["url_indietro"] = $this->generateUrl("gestione_descrittori", [
            "id_richiesta" => $richiesta->getId(),
            "id_ambito_tematico_s3_proponente" => $ambitoTematicoS3Proponente->getId(),
        ]);
        $options["has_descrizione"] = $ambitoTematicoS3Proponente->getProponente()
            ->getRichiesta()->getProcedura()->getAmbitiTematiciS3DescrizioneDescrittori();

        $options["disabled"] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
       // $options["validation_groups"] = 'default';
        $request = $this->getCurrentRequest();
        $form = $this->createForm('RichiesteBundle\Form\DescrittoreAmbitoTematicoS3ProponenteType', $descrittoreAmbitoTematicoS3Proponente, $options);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $ambitoTematicoS3Proponente = $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente();
                    $descrittoreGiaPresente = $ambitoTematicoS3Proponente->getDescrittori()
                        ->filter(function (DescrittoreAmbitoTematicoS3Proponente $descrittore) use ($descrittoreAmbitoTematicoS3Proponente) {
                        return $descrittore->getDescrittore() === $descrittoreAmbitoTematicoS3Proponente->getDescrittore();
                    });

                    if ($descrittoreGiaPresente->count()) {
                        $this->addFlash('error', 'Descrittore "' . $descrittoreAmbitoTematicoS3Proponente . '" già selezionato');
                    } else {
                        $em = $this->getEm();
                        $em->persist($descrittoreAmbitoTematicoS3Proponente);
                        $em->flush();
                        $this->addFlash('success', 'Dati salvati correttamente');
                    }

                    return new GestoreResponse($this->redirect(
                        $this->generateUrl('gestione_descrittori', [
                            "id_richiesta" => $richiesta->getId(),
                            "id_ambito_tematico_s3_proponente" => $ambitoTematicoS3Proponente->getId()
                        ]))
                    );
                } catch (Exception $e) {
                    $this->addFlash('error', 'Inserimento dell’ambito tematico S3 non andato a buon fine');
                }
            }
        }

        $options = [];
        $options["form"] = $form->createView();
        $options["richiesta"] = $richiesta;
        $options["ambito_tematico_s3"] = $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente()->getAmbitoTematicoS3();
        $options["disabled"] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
        $options["has_descrizione"] = $ambitoTematicoS3Proponente->getProponente()
            ->getRichiesta()->getProcedura()->getAmbitiTematiciS3DescrizioneDescrittori();
        $twig = 'RichiesteBundle:Richieste:aggiungiDescrittoreAmbitoTematicoS3.html.twig';
        $response = $this->render($twig, $options);
        return new GestoreResponse($response, $twig, $options);
    }

    /**
     * @param int $id_ambito_tematico_s3_proponente
     * @param int $id_descrittore
     * @return GestoreResponse|void
     */
    public function eliminaDescrittoreAmbitoTematicoS3(int $id_ambito_tematico_s3_proponente, int $id_descrittore)
    {
        $em = $this->getEm();
        $descrittoreAmbitoTematicoS3Proponente = $em->getRepository("RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente")
            ->findOneBy(['ambito_tematico_s3_proponente' => $id_ambito_tematico_s3_proponente, 'descrittore' => $id_descrittore]);
        $richiesta = $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente()->getProponente()->getRichiesta();
        try {
            $em->remove($descrittoreAmbitoTematicoS3Proponente);
            $em->flush();

            return new GestoreResponse($this->addSuccesRedirect("Ambito Tematico S3 eliminato correttamente",
                'gestione_descrittori', [
                    'id_richiesta' => $richiesta->getId(),
                    'id_ambito_tematico_s3_proponente'=> $id_ambito_tematico_s3_proponente
                ]));
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * @param int $id_ambito_tematico_s3_proponente
     * @param int $id_descrittore
     * @return GestoreResponse
     */
    public function modificaDescrittoreAmbitoTematicoS3(int $id_ambito_tematico_s3_proponente, int $id_descrittore): GestoreResponse
    {
        $em = $this->getEm();
        $descrittoreAmbitoTematicoS3Proponente = $em->getRepository("RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente")
            ->findOneBy(['ambito_tematico_s3_proponente' => $id_ambito_tematico_s3_proponente, 'descrittore' => $id_descrittore]);

        $richiesta = $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente()->getProponente()->getRichiesta();
        $options["url_indietro"] = $this->generateUrl("gestione_descrittori", [
            "id_richiesta" => $richiesta->getId(),
            "id_ambito_tematico_s3_proponente" => $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente()->getId(),
        ]);
        $options["has_descrizione"] = $descrittoreAmbitoTematicoS3Proponente
            ->getAmbitoTematicoS3Proponente()->getProponente()
            ->getRichiesta()->getProcedura()->getAmbitiTematiciS3DescrizioneDescrittori();
        $options["disabled"] = $this->container->get("gestore_richieste")->getGestore()->isRichiestaDisabilitata();
        $request = $this->getCurrentRequest();
        $form = $this->createForm('RichiesteBundle\Form\DescrittoreAmbitoTematicoS3ProponenteType', $descrittoreAmbitoTematicoS3Proponente, $options);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($descrittoreAmbitoTematicoS3Proponente);
                    $em->flush();
                    $this->addFlash('success', 'Dati salvati correttamente');

                    return new GestoreResponse($this->redirect(
                        $this->generateUrl('gestione_descrittori', [
                            "id_richiesta" => $richiesta->getId(),
                            "id_ambito_tematico_s3_proponente" => $descrittoreAmbitoTematicoS3Proponente
                                ->getAmbitoTematicoS3Proponente()->getId()
                        ]))
                    );
                } catch (Exception $e) {
                    $this->addFlash('error', 'Inserimento dell’ambito tematico S3 non andato a buon fine');
                }
            }
        }

        $options["form"] = $form->createView();
        $options["ambito_tematico_s3"] = $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente()->getAmbitoTematicoS3();
        $twig = 'RichiesteBundle:Richieste:aggiungiDescrittoreAmbitoTematicoS3.html.twig';
        $response = $this->render($twig, $options);
        return new GestoreResponse($response, $twig, $options);
    }
}
