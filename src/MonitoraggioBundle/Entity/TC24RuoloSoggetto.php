<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:22
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC24RuoloSoggettoRepository")
 * @ORM\Table(name="tc24_ruolo_soggetto")
 */
class TC24RuoloSoggetto extends EntityLoggabileCancellabile {
    use Id;

    const PROGRAMMATORE = 1;
    const BENEFICIARIO = 2;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $cod_ruolo_sog;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_ruolo_soggetto;

    /**
     * @return string
     */
    public function getCodRuoloSog() {
        return $this->cod_ruolo_sog;
    }

    /**
     * @param string $cod_ruolo_sog
     * @return self
     */
    public function setCodRuoloSog($cod_ruolo_sog) {
        $this->cod_ruolo_sog = $cod_ruolo_sog;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneRuoloSoggetto() {
        return $this->descrizione_ruolo_soggetto;
    }

    /**
     * @param string $descrizione_ruolo_soggetto
     * @return self
     */
    public function setDescrizioneRuoloSoggetto($descrizione_ruolo_soggetto) {
        $this->descrizione_ruolo_soggetto = $descrizione_ruolo_soggetto;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->cod_ruolo_sog . ' - ' . $this->descrizione_ruolo_soggetto;
    }
}
