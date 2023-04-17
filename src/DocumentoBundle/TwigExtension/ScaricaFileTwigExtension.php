<?php

namespace DocumentoBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use DocumentoBundle\Entity\Documento;

class ScaricaFileTwigExtension extends \Twig_Extension {

    private $container;

    function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function getName() {
        return 'base_mostra_scarica_file';
    }

    /**
     * @param id|DocumentoFile
     * @return string
     * @throws \Exception
     * @throws \Twig_Error
     */
    public function scaricaFile($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDati($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraScaricaFile.html.twig", $parametri);
    }

    /**
     * @param id|DocumentoFile
     * @return string
     * @throws \Exception
     * @throws \Twig_Error
     */
    public function mostraFile($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDati($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFile.html.twig", $parametri);
    }

    public function mostraFileData($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDati($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileData.html.twig", $parametri);
    }

    public function mostraFileDettaglioUtente($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDatiUtente($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileDettaglioUtente.html.twig", $parametri);
    }

    public function mostraFileTable($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDati($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileTable.html.twig", $parametri);
    }

    public function mostraFileIstruttoria($documento, $pathIstruttoria = null, $mostra_tasto_istruttoria = true) {
        $idDocumentoOrDocumentoFile = $documento->getDocumentoFile();
        $parametri = $this->getDati($idDocumentoOrDocumentoFile);
        $parametri["pathIstruttoria"] = $pathIstruttoria;
        $parametri["istruttoria"] = $documento->getIstruttoriaOggettoPagamento();
        $parametri["mostra_tasto_istruttoria"] = $mostra_tasto_istruttoria;
        $parametri["oggetto_documento"] = $documento;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileIstruttoria.html.twig", $parametri);
    }

    public function mostraFileIstruttoriaBando7($documento, $pathElimina = null, $pathModifica = null) {

        $documentoFile = $documento->getDocumentoFile();

        $parametri["documentoIstruttorio"] = $documento;
        $parametri["documentoFile"] = $documentoFile;
        $parametri["path"] = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        ;
        $parametri["pathElimina"] = $pathElimina;
        $parametri["pathModifica"] = $pathModifica;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileIstruttoriaBando7.html.twig", $parametri);
    }

    public function mostraFileIstruttoriaBando8($documento, $pathElimina = null, $pathModifica = null) {

        $documentoFile = $documento->getDocumentoFile();

        $parametri["documentoIstruttorio"] = $documento;
        $parametri["documentoFile"] = $documentoFile;
        $parametri["path"] = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        ;
        $parametri["pathElimina"] = $pathElimina;
        $parametri["pathModifica"] = $pathModifica;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileIstruttoriaBando8.html.twig", $parametri);
    }

    public function mostraFileDettaglioUtenteTr($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDatiUtente($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileDettaglioUtenteTr.html.twig", $parametri);
    }

    public function mostraFileIstruttoriaPagamento($documentoIstruttoriaPagamento, $pathElimina = null, $pathModifica = null) {

        $documentoFile = $documentoIstruttoriaPagamento->getDocumentoFile();

        $parametri["documentoIstruttoriaPagamento"] = $documentoIstruttoriaPagamento;
        $parametri["path"] = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        $parametri["pathElimina"] = $pathElimina;
        $parametri["pathModifica"] = $pathModifica;

        return $this->container->get("templating")->render("DocumentoBundle::mostraFileIstruttoriaPagamento.html.twig", $parametri);
    }

    public function mostraFileChecklistPagamento($documentoChecklistPagamento, $pathElimina = null) {

        $documentoFile = $documentoChecklistPagamento->getDocumentoFile();

        $parametri["documentoChecklistPagamento"] = $documentoChecklistPagamento;
        $parametri["path"] = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        ;
        $parametri["pathElimina"] = $pathElimina;

        return $this->container->get("templating")->render("DocumentoBundle::mostraFileChecklistPagamento.html.twig", $parametri);
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('scarica_file', array($this, 'scaricaFile'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file', array($this, 'mostraFile'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_data', array($this, 'mostraFileData'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_table', array($this, 'mostraFileTable'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_istruttoria', array($this, 'mostraFileIstruttoria'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_istruttoria_bando7', array($this, 'mostraFileIstruttoriaBando7'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_istruttoria_bando8', array($this, 'mostraFileIstruttoriaBando8'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_tr', array($this, 'mostraFileTr'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_dettaglio_utente', array($this, 'mostraFileDettaglioUtente'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_dettaglio_utente_tr', array($this, 'mostraFileDettaglioUtenteTr'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_istruttoria_pagamento', array($this, 'mostraFileIstruttoriaPagamento'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('mostra_file_checklist_pagamento', array($this, 'mostraFileChecklistPagamento'), array('is_safe' => array('html'))),
        );
    }

    private function getDati($idDocumentoOrDocumentoFile) {
        if (is_numeric($idDocumentoOrDocumentoFile)) {
            $documentoFile = $this->container->get("doctrine")->getRepository("DocumentoBundle:DocumentoFile")->find($idDocumentoOrDocumentoFile);
        } else {
            if (get_class($idDocumentoOrDocumentoFile) == 'RichiesteBundle\Entity\DocumentoProponente') {
                $documentoFileTMP = $idDocumentoOrDocumentoFile->getDocumentoFile();
                $documentoFile = $this->container->get("doctrine")->getRepository("DocumentoBundle:DocumentoFile")->find($documentoFileTMP->getId());
            } else {
                $documentoFileTMP = $idDocumentoOrDocumentoFile;
                $documentoFile = $this->container->get("doctrine")->getRepository("DocumentoBundle:DocumentoFile")->find($documentoFileTMP->getId());
            }
        }
        if ($documentoFile->getTipologiaDocumento()->isFirmaDigitale() || $documentoFile->isP7m()) {
            $firmato = true; //TODO mettere un effettivo controllo sulla firma del file
            $nomeFirmato = $documentoFile->getNomeOriginale();
            $nome = $this->container->get("documenti")->getNomePreFirma($documentoFile);
        } else {
            $firmato = false;
            $nomeFirmato = "";
            $nome = $documentoFile->getNomeOriginale();
        }

        $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        return array("documento" => $documentoFile,
            "path" => $path,
            "firmato" => $firmato,
            "nome" => $nome,
            "nomeFirmato" => $nomeFirmato
        );
    }

    private function getDatiUtente($idDocumentoOrDocumentoFile) {
        if (is_numeric($idDocumentoOrDocumentoFile)) {
            $documentoFile = $this->container->get("doctrine")->getRepository("DocumentoBundle:DocumentoFile")->find($idDocumentoOrDocumentoFile);
        } else {
            if (get_class($idDocumentoOrDocumentoFile) == 'RichiesteBundle\Entity\DocumentoProponente') {
                $documentoFile = $idDocumentoOrDocumentoFile->getDocumentoFile();
            } else {
                $documentoFile = $idDocumentoOrDocumentoFile;
            }
        }

        if ($documentoFile->getTipologiaDocumento()->isFirmaDigitale() || $documentoFile->isP7m()) {
            $firmato = true; //TODO mettere un effettivo controllo sulla firma del file
            $nomeFirmato = $documentoFile->getNomeOriginale();
            $nome = $this->container->get("documenti")->getNomePreFirma($documentoFile);
        } else {
            $firmato = false;
            $nomeFirmato = "";
            $nome = $documentoFile->getNomeOriginale();
        }
        //$utente = $this->container->get("doctrine")->getRepository("SfingeBundle:Utente")->findOneBy(array('username' => $documentoFile->getCreatoDa()));
        //$persona = $utente->getPersona();
        $persona = $this->container->get("doctrine")->getRepository("SfingeBundle:Utente")->findPersonaByUsername($documentoFile->getCreatoDa());
        if (count($persona) != 0) {
            $persona_out = $persona[0]['nome'] . ' ' . $persona[0]['cognome'];
        } else {
            $persona_out = $documentoFile->getCreatoDa();
        }

        $path = $this->container->get("funzioni_utili")->encid($documentoFile->getPath() . $documentoFile->getNome());
        return array("documento" => $documentoFile,
            "path" => $path,
            "firmato" => $firmato,
            "nome" => $nome,
            "nomeFirmato" => $nomeFirmato,
            "persona" => $persona_out,
            "datacreazione" => $documentoFile->getDataCreazione()
        );
    }

    public function mostraFileTr($idDocumentoOrDocumentoFile, $pathElimina = null) {
        $parametri = $this->getDati($idDocumentoOrDocumentoFile);
        $parametri["pathElimina"] = $pathElimina;
        return $this->container->get("templating")->render("DocumentoBundle::mostraFileTr.html.twig", $parametri);
    }

    public function getTests() {
        return [
            new \Twig_SimpleTest('firmato', function (Documento $doc) {
                    return $doc->getTipologiaDocumento()->isFirmaDigitale();
                }),
        ];
    }

    public function getFilters() {
        return [
            new \Twig_SimpleFilter('path', [$this, 'getPath']),
        ];
    }

    public function getPath(Documento $documento) {
        return $this->container->get("funzioni_utili")->encid($documento->getPath() . $documento->getNome());
    }

}
