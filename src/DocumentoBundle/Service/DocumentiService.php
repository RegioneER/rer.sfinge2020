<?php

namespace DocumentoBundle\Service;

use function copy;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Entity\Documento;
use Exception;
use function file_exists;
use Gdbnet\FatturaElettronica\FatturaElettronicaHtmlPrinter;
use Gdbnet\FatturaElettronica\FatturaElettronicaPdfPrinter;
use function get_class;
use function is_null;
use function md5_file;
use Symfony\Component\HttpFoundation\File\File;
use \Symfony\Component\HttpFoundation\File\UploadedFile;
use \Symfony\Component\HttpFoundation\Response;
use DocumentoBundle\Component\GestioneFirmaDigitale;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use RichiesteBundle\Entity\Richiesta;
use function sys_get_temp_dir;
use function unlink;
use function urldecode;

/**
 */
class DocumentiService {

    private $CADirectory;
    private $serviceContainer;

    /**
     *
     * @var \DocumentoBundle\Component\GestioneFirmaDigitale
     */
    private $gestioneFirmaDigitale;

    public function __construct(Container $serviceContainer) {
        $this->serviceContainer = $serviceContainer;
        $this->CADirectory = __DIR__ . '/../../../app/Resources/ca/caitalia.pem';
    }

    public function getGestioneFirmaDigitale() {
        return $this->gestioneFirmaDigitale;
    }

    public function initGestioneFirmaDigitale() {
        if (is_null($this->gestioneFirmaDigitale)) {
            $this->gestioneFirmaDigitale = new GestioneFirmaDigitale();
            $this->gestioneFirmaDigitale->setCADirectory($this->CADirectory);
        }
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param bool $firmaRichiesta se è true viene verificata la firma digitale
     * @param string $userCf se è definito e verificadocumento è true viene verificato che il codice fiscale del certificato 
     * 					   corrisponda a quello dell'utente
     * @return boolean
     */
    public function verificaDocumento(UploadedFile $file, $verificaCf = false, $userCf = null, $mimeTypeAmmessi = array('application/pdf')) {

        $pathFile = $file->getRealPath();
        $fileSize = $file->getSize();
        $userCfArray = array();

        if (is_array($userCf) == true) {
            $userCfArray = $userCf;
        } else {
            $userCfArray = explode(',', $userCf);
        }

        if ($fileSize == 0) {
            return array('error' => 'Il file che si sta tentando di caricare risulta essere vuoto');
        }

        $this->gestioneFirmaDigitale = new GestioneFirmaDigitale();
        $this->gestioneFirmaDigitale->setCADirectory($this->CADirectory);

//        $numeroDiFirme = 1;
//        if (strstr($file->getClientOriginalName(), 'p7m') !== false) {
//			$numeroDiFirme = substr_count($file->getClientOriginalName(), '.p7m');
//		}

        /** 
         * Carico il documento, verifico la firma ed estraggo il certificato
         */
        $resp = $this->gestioneFirmaDigitale->loadDocumentFromPath($pathFile);

        if (!$resp) {
            return array('error' => 'Errore interno');
        }

        $resp = $this->gestioneFirmaDigitale->verificaDocumento(true, true);

        if ($resp === -1) {
            return array('error' => 'Errore interno');
        }

        // Ciclo nel caso in cui il file abbia firme multiple
        //for ($i = 1; $i <= $numeroDiFirme - 1; $i++) {
        //Metto while sul mime del file che dovrebbe essere più sicuro rispetto alla sola estensione del file
        while(in_array($this->gestioneFirmaDigitale->getDocumentoInternoMimeType(), array('application/pkcs7-mime','application/binary','application/octet-stream'))) {
            // Prendo il contenuto del file precedentemente estrapolato
            $contenutoDocumentoInterno = $this->gestioneFirmaDigitale->getContenutoDocumentoInterno();
            $resp = $this->gestioneFirmaDigitale->loadDocumentFromContent($contenutoDocumentoInterno);

            if (!$resp) {
                return array('error' => 'Errore interno');
            }

            $resp = $this->gestioneFirmaDigitale->verificaDocumento(true, true);

            if ($resp === -1) {
                return array('error' => 'Errore interno');
            }

            if ($resp === false) {
                return array('error' => 'La verifica della firma digitale ha dato esito negativo');
            }
        }
        
        /**
         * controllo che il mimeType del documento contenuto dentro il P7M
         * sia tra quelli ammessi
         */
        $mime = $this->gestioneFirmaDigitale->getDocumentoInternoMimeType();

        if (\is_null($mime) || $mime == "") {
            return array('error' => 'Errore interno');
        }

        //Modifica richiesta per permettere il caricamento di ogni tipo file
        if (!in_array($mime, $mimeTypeAmmessi)) {
            return array('error' => 'Il formato del documento che si prova a caricare non è ammesso');
        }

        /*
         * Modfica per permettere di verificare più cf 
         * una casistica è quella della delega in modo che si possa controllare 
         * sia la firma del delegato sia quella del delegante e così non blocchiamo nessuno  
         *          
         */
        if ($verificaCf) {
            if (count($userCfArray) == 0) {
                return array('error' => 'Il codice fiscale di validazione non è stato indicato');
            }
            foreach ($userCfArray as $cf) {
                if ($this->gestioneFirmaDigitale->verifySubjectCf($cf) == true) {
                    return true;
                }
            }
        }
        return array('error' => 'Il codice fiscale associato alla firma digitale non corrisponde a quello del firmatario indicato');
    }

    public function getRealPath($richiesta, $tipologia_documento = null) {

        $path = $this->serviceContainer->getParameter("file.path_base");

        if (substr($path, -1) != '/') {
            $path = $path . "/";
        }

        if ($richiesta) {
            //$fase = strtolower($richiesta->getProcedura()->getFase()->getCodice());
            $id_procedura = $richiesta->getProcedura()->getId();
            $id_richiesta = $richiesta->getId();
            $path = $path . "pre" . "/" . $id_procedura . "/" . $id_richiesta . "/";
            if (!is_dir($path)) {
                $fs = new Filesystem();
                try {
                    $fs->mkdir($path);
                } catch (IOExceptionInterface $e) {
                    throw $e;
                }
            }
        } elseif ($tipologia_documento != '') {
            $path = $path . $tipologia_documento . "/";
        }

        return $path;
    }

    public function carica(DocumentoFile &$documentoFile, $flusha = false, Richiesta $richiesta = null) {

        $file = $documentoFile->getFile();
        $em = $this->getEm();

        if (is_null($file)) {
            throw new Exception("Errore nel caricamento del file");
        }

        $pathFile = $file->getRealPath();
        $originalFileName = urldecode($file->getClientOriginalName());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $destinazione = null;
        try {

            if (!file_exists($pathFile)) {
                throw new Exception("Errore nel caricamento del file");
            }
            $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $originalFileName);

            $prefix = $documentoFile->getTipologiaDocumento()->getPrefix();
            $path = $this->getRealPath($richiesta, $documentoFile->getTipologiaDocumento()->getTipologia());

            $nome = str_replace(' ', '_', $prefix . "_" . $this->getMicroTime() . "_" . $originalFileName);
            $destinazione = $path . $nome;
            $file->move($path, $nome);
            $md5 = md5_file($destinazione);

            $documentoFile->setNomeOriginale($originalFileName);
            $documentoFile->setMimeType($mimeType);
            $documentoFile->setFileSize($fileSize);
            $documentoFile->setMd5($md5);
            $documentoFile->setNome($nome);
            $documentoFile->setPath($path);

            $em->persist($documentoFile);
            if ($flusha) {
                $em->flush($documentoFile);
            }
            return $documentoFile;
        } catch (Exception $e) {
            if (!is_null($destinazione)) {
                if (file_exists($destinazione)) {
                    unlink($destinazione);
                }
            }
            throw $e;
        }
    }

    public function caricaDaByteArray($bytearray, $originalFileName, TipologiaDocumento $tipo, $flusha = false, Richiesta $richiesta = null) {

        $em = $this->getEm();

        $destinazione = null;
        try {

            $prefix = $tipo->getPrefix();
            $path = $this->getRealPath($richiesta);

            $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $originalFileName);
            $nome = str_replace(' ', '_', $prefix . "_" . $this->getMicroTime() . "_" . $originalFileName);
            $destinazione = $path . $nome;

            $fileSize = file_put_contents($destinazione, $bytearray);
            if ($fileSize === false) {
                throw new Exception("Errore nel caricamento del file");
            }

            $md5 = md5_file($destinazione);

            //calcolo il mime
            $mimeType = "application/octet-stream";
            if (function_exists("finfo_open")) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $destinazione);
                finfo_close($finfo);
            }

            $documentoFile = new DocumentoFile();
            $documentoFile->setNomeOriginale($originalFileName);
            $documentoFile->setMimeType($mimeType);
            $documentoFile->setFileSize($fileSize);
            $documentoFile->setMd5($md5);
            $documentoFile->setNome($nome);
            $documentoFile->setPath($path);
            $documentoFile->setTipologiaDocumento($tipo);

            $em->persist($documentoFile);
            if ($flusha) {
                $em->flush($documentoFile);
            }
            return $documentoFile;
        } catch (Exception $e) {
            if (!is_null($destinazione)) {
                if (file_exists($destinazione)) {
                    unlink($destinazione);
                }
            }
            throw $e;
        }
    }

    /**
     * 
     * @param string $filePathname
     * @param string $codice_tipologia_documento
     * @param string|null $new_fileBasename=null
     * @param bool $unlink=false
     *
     * @return DocumentoFile
     * @throws Exception
     */
    public function caricaDaFile($filePathname, $codice_tipologia_documento, $flusha = false, $new_fileBasename = null, $unlink = true, $richiesta = false) {
        $stcp = false;

        try {

            if (!file_exists($filePathname))
                throw new Exception("Errore in caricaFile: impossibile trovare il file [$filePathname]");

            /* @var $TipologiaDocumento TipologiaDocumento */
            $TipologiaDocumento = $this->findTipologiaDocumento(strtoupper($codice_tipologia_documento));
            if (is_null($TipologiaDocumento))
                throw new Exception("Errore in caricaFile: impossibile trovare la tipologia documento con codice:[$codice_tipologia_documento]");
            $prefix = $TipologiaDocumento->getPrefix();

            $File = new File($filePathname);
            $mimeType = $File->getMimeType();
            $fileSize = $File->getSize();
            $fileBasename = $File->getBasename();

            $path = $this->getRealPath($richiesta, $TipologiaDocumento);

            $originalFileName = (!is_null($new_fileBasename)) ? $new_fileBasename : $fileBasename;
            $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $originalFileName);
            $nome = str_replace(' ', '_', $prefix . "_" . $this->getMicroTime() . "_" . $originalFileName);
            $destinazione = $path . $nome;

            if ($filePathname != $destinazione) {
                $stcp = copy($filePathname, $destinazione);
                if (!$stcp)
                    throw new Exception("Errore in caricaFile: impossibile copiare il file [$filePathname] -> [$destinazione]");
            }


            $md5 = md5_file($destinazione);

            $documentoFile = new DocumentoFile();
            $documentoFile->setNomeOriginale($originalFileName);
            $documentoFile->setMimeType($mimeType);
            $documentoFile->setFileSize($fileSize);
            $documentoFile->setMd5($md5);
            $documentoFile->setNome($nome);
            $documentoFile->setPath($path);
            $documentoFile->setTipologiaDocumento($TipologiaDocumento);

            $this->getEm()->persist($documentoFile);
            if ($flusha)
                $this->getEm()->flush($documentoFile);

            if ($unlink && (($filePathname != $destinazione)))
                unlink($filePathname);

            return $documentoFile;
        } catch (Exception $ex) {
            if ($stcp)
                unlink($destinazione);
            throw $ex;
        }
    }

    public function caricaDaFileImportazione($filePathname, $codice_tipologia_documento, $flusha = false, $new_fileBasename = null, $unlink = true, $richiesta = false, $copy = true) {
        $stcp = false;

        try {

            if (!file_exists($filePathname))
                throw new Exception("Errore in caricaFile: impossibile trovare il file [$filePathname]");

            /* @var $TipologiaDocumento TipologiaDocumento */
            $TipologiaDocumento = $this->findTipologiaDocumento(strtoupper($codice_tipologia_documento));
            if (is_null($TipologiaDocumento))
                throw new Exception("Errore in caricaFile: impossibile trovare la tipologia documento con codice:[$codice_tipologia_documento]");
            $prefix = $TipologiaDocumento->getPrefix();

            $File = new File($filePathname);
            $mimeType = $File->getMimeType();
            $fileSize = $File->getSize();
            $fileBasename = $File->getBasename();

            $path = $this->getRealPath($richiesta, $TipologiaDocumento);

            $originalFileName = (!is_null($new_fileBasename)) ? $new_fileBasename : $fileBasename;
            $originalFileName = preg_replace("/[^a-zA-Z0-9_. -]{1}/", "_", $originalFileName);
            $nome = str_replace(' ', '_', $prefix . "_" . $this->getMicroTime() . "_" . $originalFileName);
            $destinazione = $path . $nome;

            if ($filePathname != $destinazione) {
                $stcp = true;
                if ($copy == true) {
                    $stcp = copy($filePathname, $destinazione);
                }
                if ($stcp == false)
                    throw new Exception("Errore in caricaFile: impossibile copiare il file [$filePathname] -> [$destinazione]");
            }

            if ($copy == true) {
                $md5 = md5_file($destinazione);
            } else {
                $md5 = md5_file($filePathname);
            }

            $documentoFile = new DocumentoFile();
            $documentoFile->setNomeOriginale($originalFileName);
            $documentoFile->setMimeType($mimeType);
            $documentoFile->setFileSize($fileSize);
            $documentoFile->setMd5($md5);
            $documentoFile->setNome($nome);
            $documentoFile->setPath($path);
            $documentoFile->setTipologiaDocumento($TipologiaDocumento);

            $this->getEm()->persist($documentoFile);
            if ($flusha)
                $this->getEm()->flush($documentoFile);

            if ($unlink && (($filePathname != $destinazione)))
                unlink($filePathname);

            return $documentoFile;
        } catch (Exception $ex) {
            if ($stcp)
                unlink($destinazione);
            throw $ex;
        }
    }

    public function scaricaDaPath($file_path) {
        $documento = $this->getRepository()->cercaDaPath($file_path);
        if (is_null($documento)) {
            throw new Exception("File non trovato nel database");
        }
        return $this->scaricaDaId($documento->getId());
    }

    public function scaricaOriginaleDaPath($file_path) {
        try {
            $documento = $this->getRepository()->cercaDaPath($file_path);
            if (is_null($documento)) {
                throw new Exception("File non trovato nel database");
            }
            return $this->scaricaOriginaleDaId($documento->getId());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Recupera il documento 
     * 
     * @param int $documentoId, $utente
     * @return boolean|\Symfony\Component\HttpFoundation\Response
     */
    public function scaricaDaId($documentoId) {

        if (is_null($documentoId)) {
            throw new Exception("Nessun documento specificato");
        }
        /**
         * recupero il documento
         */
        $documento = $this->getRepository()->find($documentoId);

        if (is_null($documento)) {
            throw new Exception("Il documento richiesto non esiste");
        }


        $contenutoFile = file_get_contents($documento->getPath() . $documento->getNome());

        if ($contenutoFile === false || is_null($contenutoFile)) {
            throw new Exception("File non trovato o non letto");
        }

        /**
         * recupero il nome del file ed il mime-type
         */
        $filename = urlencode($documento->getNomeOriginale());
        $mime = $documento->getMimeType();

        $response = new Response();

        /**
         * imposto gli headers con mime-type e filename
         */
        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        /**
         * inserisco il contenuto del file nel response
         */
        $response->setContent($contenutoFile);

        return $response;
    }

    public function recuperaContenutoDaId($documentoId) {

        if (is_null($documentoId)) {
            throw new Exception("Nessun documento specificato");
        }
        /**
         * recupero il documento
         */
        $documento = $this->getRepository()->find($documentoId);

        if (is_null($documento)) {
            throw new Exception("Il documento richiesto non esiste");
        }


        $contenutoFile = file_get_contents($documento->getPath() . $documento->getNome());

        if ($contenutoFile === false || is_null($contenutoFile)) {
            throw new Exception("File non trovato o non letto");
        }

        return $contenutoFile;
    }

    /**
     * Recupera il documento originale contenuto dentro il P7M
     * 
     * @param int|null $documentoId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function scaricaOriginaleDaId($documentoId) {

        /**
         * Access Control
         * to do
         */
        if (is_null($documentoId))
            throw new Exception('Nessun documento specificato');

        /**
         * recupero il documento
         * @var DocumentoFile $documento
         */
        $documento = $this->getRepository()->find($documentoId);

        if (is_null($documento))
            throw new Exception('Il documento richiesto non esiste');

        $contenutoFile = file_get_contents($documento->getPath() . $documento->getNome());

        if ($contenutoFile === false || is_null($contenutoFile))
            throw new Exception('Errore durante il recupero del documento');

        /**
         * recupero il contenuto originale del p7m, il mime-type ed il nome
         */
        $filename = urlencode($documento->getNomeOriginale());
        $isFatturaElettronica = ($documento->isXml() && $documento->isGiustificativo()) || in_array($documento->getTipologiaDocumento()->getCodice(), array('INTEGRAZIONE_PAGAMENTO_FATTURA_ELETTRONICA', 'RISPOSTA_RICHIESTA_CHIARIMENTI_FATTURA_ELETTRONICA'));
        if ($isFatturaElettronica) {
            $mimeType = $documento->getMimeType();
            //Alcuni P7M sembrano essere text/plain per la classe ma p7m lato client
            //quindi faccio la verifica anche sull'estenzione e lo forzo a application/octet-stream
            //per permettere l'estrazione successiva
            if ($this->isP7m($filename) && !in_array($mimeType, array('application/pkcs7-mime', 'application/binary',))) {
                $mimeType = 'application/octet-stream';
            }
        }

        try {
            $resp = $this->estraiDocumentoOriginale($contenutoFile, $contenutoOriginaleFile, $mimeType);
        } catch (Exception $e) {
            throw new Exception('Il file contiene un documento non valido');
        }

        if (!$resp) {
            throw new Exception("Il file contiene un documento non valido");
        }

        try {
            if ($isFatturaElettronica) {
                $mimeType = 'text/html';
                $stampante = new FatturaElettronicaHtmlPrinter();
                try {
                    $xml = new \SimpleXMLElement($contenutoOriginaleFile);
                } catch (Exception $e) {
                    throw new Exception("Xml non valido");
                }

                if (isset($xml->FatturaElettronicaBody)) {
                    $contenutoOriginaleFile = $stampante->stampa($contenutoOriginaleFile);
                } else {
                    $contenutoOriginaleFile = $stampante->stampaPeppol($contenutoOriginaleFile);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Il file contiene una fattura elettronica non valida');
        }

        $response = new Response($contenutoOriginaleFile);

        /**
         * imposto gli headers con mime-type e filename
         */
        $response->headers->set('Content-Type', $mimeType);

        //bug 2632
        if (preg_match("/.p7m$/i", $filename))
            $filename = substr($filename, 0, -4);
        //fine bug
        $response->headers->set('Content-Disposition', "inline; filename=" . $filename);

        return $response;
    }

    /**
     * Estrae il contenuto del documento originale contenuto nel P7M
     * 
     * @param type &$contenutoFile
     * @param type &$contenutoOriginaleFile il contenuto del documento originale estratto
     * @param type &$mimeType
     * @return boolean
     */
    protected function estraiDocumentoOriginale(&$contenutoFile, &$contenutoOriginaleFile, &$mimeType) {
        if ($mimeType == 'text/xml' || $mimeType == 'application/xml' || $mimeType == 'text/plain') {
            $contenutoOriginaleFile = $contenutoFile;

            return true;
        }
        $this->gestioneFirmaDigitale = new GestioneFirmaDigitale();

        $this->gestioneFirmaDigitale->setCADirectory($this->CADirectory);
        $this->gestioneFirmaDigitale->loadDocumentFromContent($contenutoFile);

        $resp = $this->gestioneFirmaDigitale->estraiContenutoDocumentoInterno();

        if ($resp === true) {
            $contenutoOriginaleFile = $this->gestioneFirmaDigitale->getContenutoDocumentoInterno();
            $mimeType = $this->gestioneFirmaDigitale->getDocumentoInternoMimeType();

            return true;
        }

        return false;
    }

    public function getNomePreFirma(Documento $documento) {
        $ptn = "/.pdf[.p7m]+/";
        $str = $documento->getNomeOriginale();
        $rpltxt = ".pdf";
        return preg_replace($ptn, $rpltxt, $str);
    }

    private function getRepository() {
        return $this->serviceContainer->get("doctrine")->getRepository("DocumentoBundle\Entity\Documento");
    }

    private function getEm() {
        return $this->serviceContainer->get("doctrine")->getManager();
    }

    public function findTipologiaDocumento($codice) {
        return $this->getEm()->getRepository(get_class(new TipologiaDocumento()))->findOneBy(array("codice" => $codice));
    }

    public function getMicroTime() {
        //su sfinge vecchio la usavano speriamo bene
        list($usec, $sec) = explode(" ", microtime());
        $valore = ((float) $usec + (float) $sec);
        $valore = str_replace('.', '', $valore);
        $valore = str_replace(',', '', $valore);
        return $valore;
    }

    public function getSysTempDir() {

        $tempDir = sys_get_temp_dir();
        $length = strlen($tempDir);

        return $tempDir[$length - 1] == '/' ? $tempDir : $tempDir . '/';
    }

    /**
     * 
     * @param string $mode="w"
     *
     * @return array("fileTemp", "handle")
     * @throws Exception
     */
    public function apriFileTemporaneo($mode = "w") {
        try {
            $tempDir = $this->getSysTempDir();
            $fileTmp = tempnam($tempDir, "tmp");
            $handle = fopen($fileTmp, $mode);

            return array("filename" => $fileTmp, "handle" => $handle);
        } catch (Exception $e) {
            throw new Exception("Errore nella scrittura del file temporaneo");
        }
    }

    /**
     * scrivi in un file temporaneo già aperto
     *
     * @param resource[] $FileTemporaneo_array
     * @param string $dati
     *
     * @throws \DocumentoBundle\Service\Exception
     * @throws Exception
     */
    public function scriviInFileTemporaneo($FileTemporaneo_array, $dati) {
        try {
            $handle = $FileTemporaneo_array['handle'];
            $bytes = fwrite($handle, $dati);
            if ($bytes == 0) {
                throw new Exception("Errore durante la scrittura in file");
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function chiudiFileTemporaneo($FileTemporaneo_array, $unlink = false) {
        try {
            fclose($FileTemporaneo_array['handle']);
            if ($unlink)
                unlink($FileTemporaneo_array['filename']);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 
     * @param Documento $documento
     * @return boolean
     */
    public function cancella(Documento $documento, $from_fs = null) {
        $em = $this->getEm();
        if ($from_fs == 1) {
            $pathFile = $documento->getPath() . $documento->getNome();
            try {
                if (!file_exists($pathFile)) {
                    throw new \Exception("File non trovato");
                }
                unlink($pathFile);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        try {
            $em->remove($documento);
            $em->flush();
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

    public function downloadDocumento($documento, $filename = null) {
        if (is_null($documento)) {
            throw new Exception("Il documento richiesto non esiste");
        }

        $contenutoFile = file_get_contents($documento->getPath() . $documento->getNome());

        if ($contenutoFile === false || \is_null($contenutoFile)) {
            throw new \Exception("File non trovato o non letto");
        }

        /**
         * recupero il nome del file ed il mime-type
         */
        if (is_null($filename)) {
            $filename = urlencode($documento->getNomeOriginale());
        }
        $response = new Response($contenutoFile);
        /**
         * imposto gli headers con mime-type e filename
         */
        $mimeType = $documento->getMimeType();
        $response->headers->set('Content-Type', $mimeType);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @param string $path
     * @param string|null $nomeFile
     * @return Response
     * @throws Exception
     */
    public function scaricaDaPathRaw(string $path, ?string $nomeFile = '') {
        if (is_null($path)) {
            throw new Exception("Nessun documento specificato");
        }

        try {
            $contenutoFile = file_get_contents($path);
        } catch (Exception $e) {
            throw new Exception("File non trovato o non letto");
        }

        if ($nomeFile) {
            $nomeFile = urlencode($nomeFile);
        } else {
            $nomeFile = basename($path);
        }

        $response = new Response();
        // Imposto gli headers con mime-type e filename
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', "inline; filename=" . $nomeFile);
        // Inserisco il contenuto del file nel response
        $response->setContent($contenutoFile);
        return $response;
    }

    public function isP7m($filename) {
        $basename1 = explode('.', basename($filename, '.' . 'p7m'));
        $basename2 = explode('.', basename($filename, '.' . 'P7M'));

        $est1 = end($basename1);
        $est2 = end($basename2);

        if ($est1 === 'p7m' || $est2 === 'P7M' || $est1 === 'P7M') {
            return true;
        }
        return false;
    }

}
