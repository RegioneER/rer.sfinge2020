<?php

namespace BaseBundle\Service;

use DocumentoBundle\Entity\TipologiaDocumento;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FunzioniUtili {

    protected $em;
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $this->container->get("doctrine");
    }

    public function getDataComuniFromRequest($request, $indirizzo) {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $temp = $request->request->get("persona");
            $data = $temp['luogo_residenza'];
            if (array_key_exists("stato", $data))
                $object->stato = $data["stato"];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($indirizzo) && !is_null($indirizzo->getComune())) {
                $object->provincia = $indirizzo->getComune()->getProvincia()->getId();
                $object->comune = $indirizzo->getComune()->getId();
            }
            if (!is_null($indirizzo) && !is_null($indirizzo->getStato())) {
                $object->stato = $indirizzo->getStato()->getId();
            }
        }
        return $object;
    }

    public function getDataComuniPersonaFromRequest($request, $persona) {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $data = $request->request->get("persona");
            if (array_key_exists("stato_nascita", $data))
                $object->stato = $data["stato_nascita"];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($persona) && !is_null($persona->getComune())) {
                $object->provincia = $persona->getComune()->getProvincia()->getId();
                $object->comune = $persona->getComune()->getId();
            }
            if (!is_null($persona) && !is_null($persona->getStatoNascita())) {
                $object->stato = $persona->getStatoNascita()->getId();
            }
        }
        return $object;
    }

    public function getDataComuniFromRequestSedeLegale($request, $azienda) {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $data = $request->request->get("azienda");
            if (is_null($data)) {
                $data = $request->request->get("comune_unione");
            }
            if (is_null($data)) {
                $data = $request->request->get("soggetto");
            }
            //$data = $temp['luogo_residenza'];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($azienda) && !is_null($azienda->getComune())) {
                $object->provincia = $azienda->getComune()->getProvincia()->getId();
                $object->comune = $azienda->getComune()->getId();
            }
        }
        return $object;
    }

    public function getIndirizzoSedeOperativaAzienda($request, $indirizzo = null) {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $temp = $request->request->get("sede");
            if (is_null($temp)) {
                $temp = $request->request->get("persona_fisica_sede");
            }
            $data = $temp["indirizzo"];
            if (array_key_exists("stato", $data))
                $object->stato = $data["stato"];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($indirizzo) && !is_null($indirizzo->getComune())) {
                $object->provincia = $indirizzo->getComune()->getProvincia()->getId();
                $object->comune = $indirizzo->getComune()->getId();
            }
            if (!is_null($indirizzo) && !is_null($indirizzo->getStato())) {
                $object->stato = $indirizzo->getStato()->getId();
            }
        }
        return $object;
    }

    public function getIndirizzoControlliAzienda($request, $indirizzo = null) {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $temp = $request->request->get("verbale_sopralluogo_controllo");
            $data = $temp["indirizzo"];
            if (array_key_exists("stato", $data))
                $object->stato = $data["stato"];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($indirizzo) && !is_null($indirizzo->getComune())) {
                $object->provincia = $indirizzo->getComune()->getProvincia()->getId();
                $object->comune = $indirizzo->getComune()->getId();
            }
            if (!is_null($indirizzo) && !is_null($indirizzo->getStato())) {
                $object->stato = $indirizzo->getStato()->getId();
            }
        }
        return $object;
    }

    public function getDataComuniFromRequestCatastali($request, $indirizzo) {

        $object = new \stdClass();
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $data = $request->request->get("indirizzo_catastale");
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($indirizzo) && !is_null($indirizzo->getComune())) {
                $object->provincia = $indirizzo->getComune()->getProvincia()->getId();
                $object->comune = $indirizzo->getComune()->getId();
            }
        }
        return $object;
    }

    public function getEM() {
        return $this->em;
    }

    function encid($value) {
        $value = base64_encode($value);
        $value = $this->shift_chr($value, 17, 14, 22);
        $value = urlencode($value);

        return $value;
    }

    function decid($value) {
        $value = urldecode($value);
        $value = $this->shift_chr($value, 14, 17, 9);
        $value = base64_decode($value);
        return $value;
    }

    function shift_chr($plain, $shift1, $shift2, $shift3) {
        $cipher = "";

        for ($i = 0; $i < strlen($plain); $i++) {
            $p = substr($plain, $i, 1);
            $p = ord($p);

            if (($p >= 33) && ($p <= 63)) {
                $c = $p + $shift1;
                if ($c > 63)
                    $c = $c - 31;
            }
            if (($p >= 65) && ($p <= 95)) {
                $c = $p + $shift2;
                if ($c > 95)
                    $c = $c - 31;
            }
            if (($p >= 96) && ($p <= 126)) {
                $c = $p + $shift3;
                if ($c > 126)
                    $c = $c - 31;
            } else {
                $c = $p;
            }

            $c = chr($c);
            $cipher = $cipher . $c;
        }

        return $cipher;
    }

    public function getObiettivi($request, $tipoProcedura) {

        $object = new \stdClass();
        $object->asse = null;
        $object->obiettiviSpecifici = null;

        if ($request->isMethod('POST')) {
            $procedura = $request->request->get($tipoProcedura);
            $object->asse = $procedura['asse'];
            if (isset($procedura['obiettivi_specifici'])) {
                $object->obiettiviSpecifici = $procedura['obiettivi_specifici'];
            }
            return $object;
        }

        return NULL;
    }

    public function getEstensioniFormattate($documento) {
        $estensioni = explode(',', $documento->getMimeAmmessi());
        $mimeFormattati = array();
        if ($documento->getFirmaDigitale()) {
            $mimeFormattati[] = "Caricare un file firmato digitalmente con estensione .p7m";
        } else {
            foreach ($estensioni as $key => $ext) {
                switch ($ext) {
                    case 'application/pdf':
                        $tmp = 'pdf';
                        break;
                    case 'image/jpeg':
                        $tmp = 'jpeg';
                        break;
                    case 'image/png':
                        $tmp = 'png';
                        break;
                    case 'image/tiff':
                        $tmp = 'tiff';
                        break;
                    case 'application/vnd.ms-excel':
                    case 'excel':
                        $tmp = 'Excel (.xls)';
                        break;
                    case 'application/x-xls':
                        $tmp = 'Excel (.xlsx)';
                        break;
                    case 'altro':
                        $tmp = 'Altri tipi file';
                        break;
                    case 'text/plain':
                        $tmp = 'txt';
                        break;
                    case 'application/zip':
                        $tmp = 'zip';
                        break;
                    case 'application/pkcs7-mime':
                        $tmp = 'p7m';
                        break;
                    case 'application/msword':
                    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                        $tmp = 'Word (.doc .docx)';
                        break;
                    case 'application/msword,application/vnd.ms-powerpoint':
                    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                        $tmp = 'Power Point (.ppt .pptx)';
                        break;
                    case 'application/vnd.oasis.opendocument.text':
                        $tmp = 'Writer (.odt)';
                        break;
                    case 'application/xml':
                    case 'text/xml':
                        $tmp = 'XML';
                        break;
                     case 'video/mp4':
                        $tmp = 'Video (.mp4)';
                        break;
                    default:
                        $tmp = null;
                        break;
                }

                if (!is_null($tmp) && !in_array($tmp, $mimeFormattati)) {
                    $mimeFormattati[] = $tmp;
                }
            }
        }
        return implode(", ", $mimeFormattati);
    }

    public function getDataSedeFornitoriFromRequest($request, $indirizzo, $id_procedura = null) {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $post = $request->request;
            if (!is_null($id_procedura)) {
                $temp = $request->request->get("fornitore" . $id_procedura);
            } else {
                $temp = $request->request->get("fornitore");
            }

            $data = $temp['indirizzo'];
            if (array_key_exists("stato", $data))
                $object->stato = $data["stato"];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($indirizzo) && !is_null($indirizzo->getComune())) {
                $object->provincia = $indirizzo->getComune()->getProvincia()->getId();
                $object->comune = $indirizzo->getComune()->getId();
            }
            if (!is_null($indirizzo) && !is_null($indirizzo->getStato())) {
                $object->stato = $indirizzo->getStato()->getId();
            }
        }
        return $object;
    }

    public function getDataIndirizzoInterventoFromRequest($request, $indirizzo, $typeClass = 'intervento') {

        $object = new \stdClass();
        $object->stato = null;
        $object->provincia = null;
        $object->comune = null;

        if ($request->isMethod('POST')) {
            $temp = $request->request->get($typeClass);
            $data = $temp['indirizzo'];
            if (array_key_exists("stato", $data))
                $object->stato = $data["stato"];
            if (array_key_exists("provincia", $data))
                $object->provincia = $data["provincia"];
            if (array_key_exists("comune", $data))
                $object->comune = $data["comune"];
        } else {
            if (!is_null($indirizzo) && !is_null($indirizzo->getComune())) {
                $object->provincia = $indirizzo->getComune()->getProvincia()->getId();
                $object->comune = $indirizzo->getComune()->getId();
            }
            if (!is_null($indirizzo) && !is_null($indirizzo->getStato())) {
                $object->stato = $indirizzo->getStato()->getId();
            }
        }
        return $object;
    }

    /**
     * @param TipologiaDocumento $tipologiaDocumento
     * @return array
     */
    public function getInformazioniDocumentoDropzone(TipologiaDocumento $tipologiaDocumento): array {
        $estensioni = explode(',', $tipologiaDocumento->getMimeAmmessi());
        $mimeFormattati = [];

        foreach ($estensioni as $ext) {
            switch ($ext) {
                case 'application/pdf':
                    $tmp = '.pdf';
                    break;
                case 'image/jpeg':
                    $tmp = '.jpeg';
                    break;
                case 'image/png':
                    $tmp = '.png';
                    break;
                case 'image/tiff':
                    $tmp = '.tiff';
                    break;
                case 'application/vnd.ms-excel':
                case 'excel':
                    $tmp = '.xls';
                    break;
                case 'application/x-xls':
                    $tmp = '.xlsx';
                    break;
                case 'text/plain':
                    $tmp = '.txt';
                    break;
                case 'application/zip':
                    $tmp = '.zip';
                    break;
                case 'application/pkcs7-mime':
                    $tmp = '.p7m';
                    break;
                case 'application/msword':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    $tmp = '.doc .docx';
                    break;
                case 'application/vnd.oasis.opendocument.text':
                    $tmp = '.odt';
                    break;
                case 'application/xml':
                case 'text/xml':
                    $tmp = '.xml';
                    break;
                case 'video/mp4':
                    $tmp = '.mp4';
                    break;
                default:
                    $tmp = null;
                    break;
            }

            if (!is_null($tmp) && !in_array($tmp, $mimeFormattati)) {
                $mimeFormattati[] = $tmp;
            }
        }

        return [
            'estensioni' => implode(", ", $mimeFormattati),
            'dimensione_massima' => $tipologiaDocumento->getDimensioneMassima(),
            'mime_ammessi' => $tipologiaDocumento->getMimeAmmessi(),
        ];
    }

    /**
     * @param array $arrayDiValori
     * @param string $congiunzione
     * @return string
     */
    public function concatenazioneInLinguaggioNaturale(array $arrayDiValori, string $congiunzione = 'e'): string
    {
        if (!empty($arrayDiValori)) {
            if (count($arrayDiValori) > 1) {
                $ultimoElemento = array_pop($arrayDiValori);
                $retVal = implode(', ', $arrayDiValori) .' ' . $congiunzione . ' ' . $ultimoElemento;
            } else {
                $retVal = implode(', ', $arrayDiValori);
            }

            return $retVal;
        }

        return '';
    }

    /**
     * @param $value
     * @return float
     */
    function floatvalue($value): float
    {
        $value = str_replace(',','.', $value);
        $value = preg_replace('/\.(?=.*\.)/', '', $value);
        return floatval($value);
    }
}
