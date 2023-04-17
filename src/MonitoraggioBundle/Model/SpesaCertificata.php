<?php

namespace MonitoraggioBundle\Model;

use CertificazioniBundle\Entity\CertificazionePagamento;
use CertificazioniBundle\Entity\Certificazione;
use MonitoraggioBundle\Form\Entity\LivelloGerarchico;

class SpesaCertificata {
    protected static $RomanArabicNumberMapping = array(
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    );

    /**
     * @var CertificazionePagamento
     */
    protected $certificazionePagamento;


    public function __construct(CertificazionePagamento $certificazionePagamento) {
        $this->certificazionePagamento = $certificazionePagamento;
    }

    /**
     * @return string
     */
    public function getIdDomandaPagamento() {
        $certificazione = $this->getCertificazione();
        $numeroRomanoCerficazione = $certificazione->getNumero();
        $numero = $this->covertRomanNumber($numeroRomanoCerficazione);
        return $numero.'/'.($certificazione->getAnno());
    }

    /**
     * @param string $roman
     * @return int
     */
    public function covertRomanNumber($roman) {
        $result = 0;
        foreach (self::$RomanArabicNumberMapping as $key => $value) {
            while (0 === strpos($roman, $key)) {
                $result += $value;
                $roman = substr($roman, strlen($key));
            }
        }
        return $result;
    }

    /**
     * @return \DateTime
     */
    public function getDataDomanda() {
        $certificazione = $this->getCertificazione();
        return $certificazione->getDataApprovazione();
    }

    /**
     * @return Certificazione
     */
    public function getCertificazione(){
        return $this->certificazionePagamento->getCertificazione();
    }

    /**
     * @return float
     */
    public function getImportoTotale(){
        return abs($this->certificazionePagamento->getImporto());
    }

    /**
     * @return float
     */
    public function getImportoSpesaPubblica(){
        return $this->getImportoTotale();
    }

    /**
     * @return string
     * TODO: Implementare decertificazioni
     */
    public function getTipologiaImporto(){
        return $this->certificazionePagamento->getImporto() >= 0 ? 'C' : 'D';
    }

    /**
     * @return LivelloGerarchico
     */
    public function getLivelloGerarchico(){
        return $this->certificazionePagamento
            ->getPagamento()
            ->getAttuazioneControlloRichiesta()
            ->getRichiesta()
            ->getProcedura()
            ->getAsse()
            ->getLivelloGerarchico();
    }
}
