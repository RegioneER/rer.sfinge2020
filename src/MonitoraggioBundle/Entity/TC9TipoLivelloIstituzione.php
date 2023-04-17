<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:51
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC9TipoLivelloIstituzioneRepository")
 * @ORM\Table(name="tc9_tipo_livello_istituzione")
 */
class TC9TipoLivelloIstituzione extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $liv_istituzione_str_fin;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_livello_istituzione;

    /**
     * @return mixed
     */
    public function getLivIstituzioneStrFin() {
        return $this->liv_istituzione_str_fin;
    }

    /**
     * @param mixed $liv_istituzione_str_fin
     */
    public function setLivIstituzioneStrFin($liv_istituzione_str_fin) {
        $this->liv_istituzione_str_fin = $liv_istituzione_str_fin;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneLivelloIstituzione() {
        return $this->descrizione_livello_istituzione;
    }

    /**
     * @param mixed $descrizione_livello_istituzione
     */
    public function setDescrizioneLivelloIstituzione($descrizione_livello_istituzione) {
        $this->descrizione_livello_istituzione = $descrizione_livello_istituzione;
    }

    public function __toString() {
        return $this->liv_istituzione_str_fin . ' - ' . $this->descrizione_livello_istituzione;
    }
}
