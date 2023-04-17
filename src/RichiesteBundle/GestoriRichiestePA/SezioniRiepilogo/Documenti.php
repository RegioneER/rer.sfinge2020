<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use BaseBundle\Form\CommonType;
use DocumentoBundle\Entity\DocumentoFile;
use RichiesteBundle\Entity\DocumentoRichiesta;
use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;

class Documenti extends ASezioneRichiesta {
    const TITOLO = 'Gestione allegati richiesta';
    const SOTTOTITOLO = 'Carica i documenti richiesti';
    // const VALIDATION_GROUP = 'dati_progetto';

    const NOME_SEZIONE = 'documenti';

    public function getTitolo() {
        return self::TITOLO;
    }

    public function valida() {
        $documenti_obbligatori = $this->getGestoreRichiesta()->getTipiDocumenti($this->richiesta->getId(), 1);
        $this->listaMessaggi = \array_map(function ($documento) {
            return 'Caricare il documento ' . $documento->getDescrizione();
        }, $documenti_obbligatori);
    }

    public function getUrl() {
        return $this->generateUrl(self::ROTTA, [
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
        ]);
    }

    public function visualizzaSezione(array $parametri) {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        $id_richiesta = $this->richiesta->getId();
        $gestore = $this->getGestoreRichiesta();
        $em = $this->getEm();
        $request = $this->getCurrentRequest();
        $indietro = $this->riepilogo->getUrl();
        $richiestaDisabilitata = $gestore->isRichiestaDisabilitata();
        $documentoRichiesta = new DocumentoRichiesta();
        $documentoFile = new DocumentoFile();
        $documentoRichiesta->setRichiesta($this->richiesta);
        $documentoRichiesta->setDocumentoFile($documentoFile);
        $listaTipi = $gestore->getTipiDocumenti($id_richiesta, 0);

        if (count($listaTipi) > 0 && !$richiestaDisabilitata) {
            $form = $this->createForm('DocumentoBundle\Form\Type\DocumentoFileType', $documentoFile, [
                "lista_tipi" => $listaTipi,
                "cf_firmatario" => $this->richiesta->getFirmatario()->getCodiceFiscale(),
            ]);
            $form->add('submit', CommonType::salva_indietro, [
                'label' => 'Salva',
                'url' => $indietro,
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->container->get("documenti")->carica($documentoFile, 0, $this->richiesta);
                try {
                    $em->persist($documentoRichiesta);
                    $em->flush();

                    $this->addFlash('success', "Documento caricato correttamente");
                } catch (\Exception $e) {
                    $this->container->get('logger')->error($e->getMessage());
                    $this->addError('Errore nel salvataggio delle informazioni');
                }
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
        }

        $documentiCaricati = $em->getRepository("RichiesteBundle\Entity\DocumentoRichiesta")
        ->findDocumentiCaricati($this->richiesta->getId());

        $dati = [
            "documenti" => $documentiCaricati,
            "id_richiesta" => $id_richiesta,
            "form" => $form_view,
            'is_richiesta_disabilitata' => $richiestaDisabilitata,
            'indietro' => $indietro,
        ];
        return $this->render("RichiesteBundle:Bando60:elencoDocumentiRichiesta.html.twig", $dati);
    }
}
