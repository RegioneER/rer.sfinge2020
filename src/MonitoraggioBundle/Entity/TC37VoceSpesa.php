<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:38
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC37VoceSpesaRepository")
 * @ORM\Table(name="tc37_voce_spesa")
 */
class TC37VoceSpesa extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $voce_spesa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_voce_spesa;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $codice_natura_cup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="descrizione_natura_cup")
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizionenatura_cup;

    /**
     * @return mixed
     */
    public function getVoceSpesa() {
        return $this->voce_spesa;
    }

    /**
     * @param mixed $voce_spesa
     */
    public function setVoceSpesa($voce_spesa) {
        $this->voce_spesa = $voce_spesa;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneVoceSpesa() {
        return $this->descrizione_voce_spesa;
    }

    /**
     * @param mixed $descrizione_voce_spesa
     */
    public function setDescrizioneVoceSpesa($descrizione_voce_spesa) {
        $this->descrizione_voce_spesa = $descrizione_voce_spesa;
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
    public function getDescrizionenaturaCup() {
        return $this->descrizionenatura_cup;
    }

    /**
     * @param mixed $descrizionenatura_cup
     */
    public function setDescrizionenaturaCup($descrizionenatura_cup) {
        $this->descrizionenatura_cup = $descrizionenatura_cup;
    }

    public function __toString() {
        return $this->voce_spesa . ' - ' . $this->descrizionenatura_cup . ': ' . $this->descrizione_voce_spesa;
    }
}
