<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:38
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="tc5_tipo_operazione")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC5TipoOperazioneRepository")
 */
class TC5TipoOperazione extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     * @Assert\NotNUll
     * @Assert\Length(max="11", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $tipo_operazione;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max="5", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $codice_natura_cup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_natura_cup;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max="11", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $codice_tipologia_cup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max="255", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_tipologia_cup;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max="10", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $origine_dato;

    /**
     * @return mixed
     */
    public function getTipoOperazione() {
        return $this->tipo_operazione;
    }

    /**
     * @param mixed $tipo_operazione
     */
    public function setTipoOperazione($tipo_operazione) {
        $this->tipo_operazione = $tipo_operazione;
    }

    /**
     * @return mixed
     */
    public function getCodiceNaturaCup() {
        return $this->codice_natura_cup;
    }

    /**
     * @param mixed $codice_natura_cup
     */
    public function setCodiceNaturaCup($codice_natura_cup) {
        $this->codice_natura_cup = $codice_natura_cup;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneNaturaCup() {
        return $this->descrizione_natura_cup;
    }

    /**
     * @param mixed $descrizione_natura_cup
     */
    public function setDescrizioneNaturaCup($descrizione_natura_cup) {
        $this->descrizione_natura_cup = $descrizione_natura_cup;
    }

    /**
     * @return mixed
     */
    public function getCodiceTipologiaCup() {
        return $this->codice_tipologia_cup;
    }

    /**
     * @param mixed $codice_tipologia_cup
     */
    public function setCodiceTipologiaCup($codice_tipologia_cup) {
        $this->codice_tipologia_cup = $codice_tipologia_cup;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipologiaCup() {
        return $this->descrizione_tipologia_cup;
    }

    /**
     * @param mixed $descrizione_tipologia_cup
     */
    public function setDescrizioneTipologiaCup($descrizione_tipologia_cup) {
        $this->descrizione_tipologia_cup = $descrizione_tipologia_cup;
    }

    /**
     * @return mixed
     */
    public function getOrigineDato() {
        return $this->origine_dato;
    }

    /**
     * @param mixed $origine_dato
     */
    public function setOrigineDato($origine_dato) {
        $this->origine_dato = $origine_dato;
    }

    public function __toString() {
        return $this->tipo_operazione;
    }
}
