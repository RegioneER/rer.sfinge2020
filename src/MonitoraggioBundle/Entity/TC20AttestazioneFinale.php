<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:16
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC20AttestazioneFinaleRepository")
 * @ORM\Table(name="tc20_attestazione_finale")
 */
class TC20AttestazioneFinale extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_attestazione_finale;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_attestazione_finale;

    /**
     * @return mixed
     */
    public function getCodAttestazioneFinale() {
        return $this->cod_attestazione_finale;
    }

    /**
     * @param mixed $cod_attestazione_finale
     */
    public function setCodAttestazioneFinale($cod_attestazione_finale) {
        $this->cod_attestazione_finale = $cod_attestazione_finale;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneAttestazioneFinale() {
        return $this->descrizione_attestazione_finale;
    }

    /**
     * @param mixed $descrizione_attestazione_finale
     */
    public function setDescrizioneAttestazioneFinale($descrizione_attestazione_finale) {
        $this->descrizione_attestazione_finale = $descrizione_attestazione_finale;
    }
}
