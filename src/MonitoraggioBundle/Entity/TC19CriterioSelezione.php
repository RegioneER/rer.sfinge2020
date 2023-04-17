<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:15
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC19CriterioSelezioneRepository")
 * @ORM\Table(name="tc19_criterio_selezione")
 */
class TC19CriterioSelezione extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_criterio_selezione;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_criterio_selezione;

    /**
     * @return mixed
     */
    public function getCodCriterioSelezione() {
        return $this->cod_criterio_selezione;
    }

    /**
     * @param mixed $cod_criterio_selezione
     */
    public function setCodCriterioSelezione($cod_criterio_selezione) {
        $this->cod_criterio_selezione = $cod_criterio_selezione;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCriterioSelezione() {
        return $this->descrizione_criterio_selezione;
    }

    /**
     * @param mixed $descrizione_criterio_selezione
     */
    public function setDescrizioneCriterioSelezione($descrizione_criterio_selezione) {
        $this->descrizione_criterio_selezione = $descrizione_criterio_selezione;
    }
}
