<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:26
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC27CittadinanzaRepository")
 * @ORM\Table(name="tc27_cittadinanza")
 */
class TC27Cittadinanza extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cittadinanza;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_cittadinanza;

    /**
     * @return mixed
     */
    public function getCittadinanza() {
        return $this->cittadinanza;
    }

    /**
     * @param mixed $cittadinanza
     */
    public function setCittadinanza($cittadinanza) {
        $this->cittadinanza = $cittadinanza;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCittadinanza() {
        return $this->descrizione_cittadinanza;
    }

    /**
     * @param mixed $descrizione_cittadinanza
     */
    public function setDescrizioneCittadinanza($descrizione_cittadinanza) {
        $this->descrizione_cittadinanza = $descrizione_cittadinanza;
    }
}
