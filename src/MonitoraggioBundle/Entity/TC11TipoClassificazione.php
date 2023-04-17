<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:56
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC11TipoClassificazioneRepository")
 * @ORM\Table(name="tc11_tipo_classificazione")
 */
class TC11TipoClassificazione extends EntityLoggabileCancellabile {
    use Id;

    const ATTIVITA_ECONOMICA = 'AE';
    const LINEA_AZIONE = 'LA';
    const RISULTATO_ATTESO = 'RA';

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tipo_class;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_tipo_classificazione;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $origine_classificazione;

    /**
     * @var TC12Classificazione|Collection
     * @ORM\OneToMany(targetEntity="TC12Classificazione", mappedBy="tipo_classificazione")
     */
    protected $classificazioni;

    /**
     * @return string
     */
    public function getTipoClass() {
        return $this->tipo_class;
    }

    /**
     * @param string $tipo_class
     * @return self
     */
    public function setTipoClass($tipo_class) {
        $this->tipo_class = $tipo_class;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoClassificazione() {
        return $this->descrizione_tipo_classificazione;
    }

    /**
     * @param string $descrizione_tipo_classificazione
     * @return self
     */
    public function setDescrizioneTipoClassificazione($descrizione_tipo_classificazione) {
        $this->descrizione_tipo_classificazione = $descrizione_tipo_classificazione;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigineClassificazione() {
        return $this->origine_classificazione;
    }

    /**
     * @param mixed $origine_classificazione
     */
    public function setOrigineClassificazione($origine_classificazione) {
        $this->origine_classificazione = $origine_classificazione;
    }

    public function __toString() {
        return $this->getTipoClass() . ' - ' . $this->getDescrizioneTipoClassificazione();
    }

    public function __construct() {
        $this->classificazioni = new ArrayCollection();
    }

    /**
     * Add classificazioni
     *
     * @param TC12Classificazione $classificazioni
     * @return self
     */
    public function addClassificazioni(TC12Classificazione $classificazioni) {
        $this->classificazioni[] = $classificazioni;

        return $this;
    }

    /**
     * Remove classificazioni
     *
     * @param TC12Classificazione $classificazioni
     */
    public function removeClassificazioni(TC12Classificazione $classificazioni) {
        $this->classificazioni->removeElement($classificazioni);
    }

    /**
     * Get classificazioni
     *
     * @return Collection|TC12Classificazione[]
     */
    public function getClassificazioni() {
        return $this->classificazioni;
    }
}
