<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 15:05
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity
 * @ORM\Table(name="elenco_tabelle_contesto")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\ElencoTabelleContestoRepository")
 */
class ElencoTabelleContesto extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @var string
     *
     * @ORM\Column(name="codice", type="string", length=10)
     */
    protected $codice;

    /**
     * @var string
     *
     * @ORM\Column(name="descrizione", type="string", length=100)
     */
    protected $descrizione;

    /**
     * @var string
     * @ORM\Column(name="classe_entity", type="string", length=50, nullable=true)
     */
    protected $classeEntity;

    /**
     * @param string $codice
     */
    public function setCodice($codice) {
        $this->codice = $codice;
    }

    /**
     * @return string
     */
    public function getCodice() {
        return $this->codice;
    }

    /**
     * @param string $descrizione
     */
    public function setDescrizione($descrizione) {
        $this->descrizione = $descrizione;
    }

    /**
     * @return string
     */
    public function getDescrizione() {
        return $this->descrizione;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getCodice();
    }

    public function getClasseEntity() {
        return $this->classeEntity;
    }

    public function setClasseEntity($classeEntity) {
        $this->classeEntity = $classeEntity;
    }
}
