<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:43
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC40TipoPercettoreRepository")
 * @ORM\Table(name="tc40_tipo_percettore")
 */
class TC40TipoPercettore extends EntityLoggabileCancellabile {
    use Id;

    const IMPRESE = '3';

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tipo_percettore;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_tipo_percettore;

    /**
     * @return mixed
     */
    public function getTipoPercettore() {
        return $this->tipo_percettore;
    }

    /**
     * @param mixed $tipo_percettore
     */
    public function setTipoPercettore($tipo_percettore) {
        $this->tipo_percettore = $tipo_percettore;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoPercettore() {
        return $this->descrizione_tipo_percettore;
    }

    /**
     * @param mixed $descrizione_tipo_percettore
     */
    public function setDescrizioneTipoPercettore($descrizione_tipo_percettore) {
        $this->descrizione_tipo_percettore = $descrizione_tipo_percettore;
    }

    public function __toString() {
        return $this->getTipoPercettore() . ' - ' . $this->getDescrizioneTipoPercettore();
    }
}
