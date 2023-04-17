<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 05/06/17
 * Time: 15:41
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Table(name="tc6_tipo_aiuto")
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC6TipoAiutoRepository")
 */
class TC6TipoAiuto extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\Length(max="2", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $tipo_aiuto;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(max="100", maxMessage="Massimo {{ limit }} caratteri")
     */
    protected $descrizione_tipo_aiuto;

    /**
     * @return mixed
     */
    public function getTipoAiuto() {
        return $this->tipo_aiuto;
    }

    /**
     * @param mixed $tipo_aiuto
     */
    public function setTipoAiuto($tipo_aiuto) {
        $this->tipo_aiuto = $tipo_aiuto;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipoAiuto() {
        return $this->descrizione_tipo_aiuto;
    }

    /**
     * @param mixed $descrizione_tipo_aiuto
     */
    public function setDescrizioneTipoAiuto($descrizione_tipo_aiuto) {
        $this->descrizione_tipo_aiuto = $descrizione_tipo_aiuto;
    }

    public function __toString() {
        return $this->tipo_aiuto . ' - ' . $this->descrizione_tipo_aiuto;
    }
}
