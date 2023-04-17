<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:54
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC10TipoLocalizzazioneRepository")
 * @ORM\Table(name="tc10_tipo_localizzazione")
 */
class TC10TipoLocalizzazione extends EntityLoggabileCancellabile {
    use Id;

    const PUNTUALE = 'C';

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tipo_localizzazione;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri ")
     */
    protected $descrizione_tipo_localizzazione;

    /**
     * @return mixed
     */
    public function getTipoLocalizzazione() {
        return $this->tipo_localizzazione;
    }

    /**
     * @param mixed $tipo_localizzazione
     */
    public function setTipoLocalizzazione($tipo_localizzazione) {
        $this->tipo_localizzazione = $tipo_localizzazione;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoLocalizzazione() {
        return $this->descrizione_tipo_localizzazione;
    }

    /**
     * @param mixed $descrizione_tipo_localizzazione
     */
    public function setDescrizioneTipoLocalizzazione($descrizione_tipo_localizzazione) {
        $this->descrizione_tipo_localizzazione = $descrizione_tipo_localizzazione;
    }

    public function __toString() {
        return $this->tipo_localizzazione . ' - ' . $this->descrizione_tipo_localizzazione;
    }
}
