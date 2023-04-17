<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:33
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC32StatoPartecipanteRepository")
 * @ORM\Table(name="tc32_stato_partecipante")
 */
class TC32StatoPartecipante extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $stato_partecipante;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_stato_partecipante;

    /**
     * @return mixed
     */
    public function getStatoPartecipante() {
        return $this->stato_partecipante;
    }

    /**
     * @param mixed $stato_partecipante
     */
    public function setStatoPartecipante($stato_partecipante) {
        $this->stato_partecipante = $stato_partecipante;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneStatoPartecipante() {
        return $this->descrizione_stato_partecipante;
    }

    /**
     * @param mixed $descrizione_stato_partecipante
     */
    public function setDescrizioneStatoPartecipante($descrizione_stato_partecipante) {
        $this->descrizione_stato_partecipante = $descrizione_stato_partecipante;
    }
}
