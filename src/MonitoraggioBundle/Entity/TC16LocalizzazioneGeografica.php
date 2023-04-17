<?php

/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:11
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC16LocalizzazioneGeograficaRepository")
 * @ORM\Table(name="tc16_localizzazione_geografica")
 */
class TC16LocalizzazioneGeografica extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $codice_regione;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_regione;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $codice_provincia;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_provincia;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $codice_comune;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_comune;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $nuts_1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $nuts_2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $nuts_3;

    /**
     * @return string
     */
    public function getCodiceRegione() {
        return $this->codice_regione;
    }

    /**
     * @param string $codice_regione
     * @return self
     */
    public function setCodiceRegione($codice_regione) {
        $this->codice_regione = $codice_regione;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneRegione() {
        return $this->descrizione_regione;
    }

    /**
     * @param string $descrizione_regione
     * @return self
     */
    public function setDescrizioneRegione($descrizione_regione) {
        $this->descrizione_regione = $descrizione_regione;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodiceProvincia() {
        return $this->codice_provincia;
    }

    /**
     * @param string $codice_provincia
     * @return self
     */
    public function setCodiceProvincia($codice_provincia) {
        $this->codice_provincia = $codice_provincia;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneProvincia() {
        return $this->descrizione_provincia;
    }

    /**
     * @param string $descrizione_provincia
     * @return self
     */
    public function setDescrizioneProvincia($descrizione_provincia) {
        $this->descrizione_provincia = $descrizione_provincia;
    }

    /**
     * @return string
     */
    public function getCodiceComune() {
        return $this->codice_comune;
    }

    public function setCodiceComune(?string $codice_comune): self {
        $this->codice_comune = $codice_comune;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneComune() {
        return $this->descrizione_comune;
    }

    /**
     * @param mixed $descrizione_comune
     * @return self
     */
    public function setDescrizioneComune($descrizione_comune) {
        $this->descrizione_comune = $descrizione_comune;
        return $this;
    }

    /**
     * @return string
     */
    public function getNuts1() {
        return $this->nuts_1;
    }

    /**
     * @param string $nuts_1
     * @return self
     */
    public function setNuts1($nuts_1) {
        $this->nuts_1 = $nuts_1;
        return $this;
    }

    /**
     * @return string
     */
    public function getNuts2() {
        return $this->nuts_2;
        return $this;
    }

    /**
     * @param string $nuts_2
     * @return self
     */
    public function setNuts2($nuts_2) {
        $this->nuts_2 = $nuts_2;
        return $this;
    }

    /**
     * @return string
     */
    public function getNuts3() {
        return $this->nuts_3;
    }

    /**
     * @param string $nuts_3
     * @return self
     */
    public function setNuts3($nuts_3) {
        $this->nuts_3 = $nuts_3;
        return $this;
    }

    public function __toString() {
        return $this->descrizione_comune . ' (' . $this->descrizione_comune . ')';
    }

    public function getCodLocalizzazione() {
        return $this->codice_regione . $this->codice_provincia . $this->codice_comune;
    }

    /**
     * @param string $stringa concatenazione codici
     * @return array codice regione, codice provincia, codice comune
     */
    public static function GetCodici($stringa) {
        $match = [];
        \preg_match('/(.{2,3})(.{3})(.{3})/', $stringa, $match);
        \array_shift($match);
        return $match;
    }
}
