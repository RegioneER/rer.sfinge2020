<?php

namespace AttuazioneControlloBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="AttuazioneControlloBundle\Entity\TipologiaGiustificativoRepository")
 * @ORM\Table(name="tipologie_giustificativo")
 */
class TipologiaGiustificativo extends EntityTipo
{

    const TIPOLOGIA_SPESE_PERSONALE              = 'TIPOLOGIA_SPESE_PERSONALE';
    const TIPOLOGIA_STANDARD_FATTURA_ELETTRONICA = 'TIPOLOGIA_STANDARD_FATTURA_ELETTRONICA';

    /**
     * @ORM\ManyToMany(targetEntity="SfingeBundle\Entity\Procedura", inversedBy="tipologie_giustificativo")
     * @ORM\JoinTable(name="tipologie_giustificativo_procedure")
     */
    protected $procedure;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $invisibile;

    /**
     * @var string $descrizione
     *
     * @ORM\Column(name="descrizione_piano_costi", type="string", length=1000)
     */
    protected $descrizione_piano_costi;

    /**
     * @var string $descrizione
     *
     * @ORM\Column(name="descrizione_tab_giustificativi", type="string", length=1000)
     */
    protected $descrizione_tab_giustificativi;

    /**
     * @return string|null
     */
    public function __toString()
    {
        return $this->descrizione;
    }

    /**
     * @return mixed
     */
    public function getProcedure()
    {
        return $this->procedure;
    }

    /**
     * @param $procedure
     */
    public function setProcedure($procedure)
    {
        $this->procedure = $procedure;
    }

    /**
     * @return mixed
     */
    public function getInvisibile()
    {
        return $this->invisibile;
    }

    /**
     * @param $invisibile
     */
    public function setInvisibile($invisibile)
    {
        $this->invisibile = $invisibile;
    }

    /**
     * @return string
     */
    public function getDescrizionePianoCosti()
    {
        return $this->descrizione_piano_costi;
    }

    /**
     * @return string
     */
    public function getDescrizioneTabGiustificativi()
    {
        return $this->descrizione_tab_giustificativi;
    }

    /**
     * @param $descrizione_piano_costi
     */
    public function setDescrizionePianoCosti($descrizione_piano_costi)
    {
        $this->descrizione_piano_costi = $descrizione_piano_costi;
    }

    /**
     * @param $descrizione_tab_giustificativi
     */
    public function setDescrizioneTabGiustificativi($descrizione_tab_giustificativi)
    {
        $this->descrizione_tab_giustificativi = $descrizione_tab_giustificativi;
    }

    /**
     * @return bool
     */
    public function isInvisibile()
    {
        return $this->invisibile == true;
    }

    /**
     * @return bool
     */
    public function isTipologiaSpesePersonale()
    {
        return $this->codice == self::TIPOLOGIA_SPESE_PERSONALE;
    }

    /**
     * @return bool
     */
    public function isTipologiaFatturaElettronica()
    {
        return$this->codice === self::TIPOLOGIA_STANDARD_FATTURA_ELETTRONICA;
    }
}

