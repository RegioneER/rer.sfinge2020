<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:27
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC29CondizioneMercatoRepository")
 * @ORM\Table(name="tc29_condizione_mercato")
 */
class TC29CondizioneMercato extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cond_mercato_ingresso;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_condizione_mercato;

    /**
     * @return mixed
     */
    public function getCondMercatoIngresso() {
        return $this->cond_mercato_ingresso;
    }

    /**
     * @param mixed $cond_mercato_ingresso
     */
    public function setCondMercatoIngresso($cond_mercato_ingresso) {
        $this->cond_mercato_ingresso = $cond_mercato_ingresso;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCondizioneMercato() {
        return $this->descrizione_condizione_mercato;
    }

    /**
     * @param mixed $descrizione_condizione_mercato
     */
    public function setDescrizioneCondizioneMercato($descrizione_condizione_mercato) {
        $this->descrizione_condizione_mercato = $descrizione_condizione_mercato;
    }
}
