<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:21
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC23TipoProceduraAggiudicazioneRepository")
 * @ORM\Table(name="tc23_tipo_procedura_aggiudicazione")
 */
class TC23TipoProceduraAggiudicazione extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tipo_proc_agg;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_tipologia_procedura_aggiudicazione;

    /**
     * @return mixed
     */
    public function getTipoProcAgg() {
        return $this->tipo_proc_agg;
    }

    /**
     * @param mixed $tipo_proc_agg
     */
    public function setTipoProcAgg($tipo_proc_agg) {
        $this->tipo_proc_agg = $tipo_proc_agg;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneTipologiaProceduraAggiudicazione() {
        return $this->descrizione_tipologia_procedura_aggiudicazione;
    }

    /**
     * @param mixed $descrizione_tipologia_procedura_aggiudicazione
     */
    public function setDescrizioneTipologiaProceduraAggiudicazione($descrizione_tipologia_procedura_aggiudicazione) {
        $this->descrizione_tipologia_procedura_aggiudicazione = $descrizione_tipologia_procedura_aggiudicazione;
    }

    public function __toString() {
        return $this->getTipoProcAgg() . ' - ' . $this->getDescrizioneTipologiaProceduraAggiudicazione();
    }
}
