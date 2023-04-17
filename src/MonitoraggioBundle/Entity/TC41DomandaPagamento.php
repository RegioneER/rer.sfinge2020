<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:45
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC41DomandaPagamentoRepository")
 * @ORM\Table(name="tc41_domanda_pagamento")
 */
class TC41DomandaPagamento extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=100, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $id_domanda_pagamento;

    /**
     * @ORM\ManyToOne(targetEntity="TC4Programma")
     * @ORM\JoinColumn(name="programma_id", referencedColumnName="id")
     */
    protected $programma;

    /**
     * @ORM\ManyToOne(targetEntity="TC33FonteFinanziaria")
     * @ORM\JoinColumn(name="fondo_id", referencedColumnName="id")
     */
    protected $fondo;

    /**
     * @return mixed
     */
    public function getIdDomandaPagamento() {
        return $this->id_domanda_pagamento;
    }

    /**
     * @param mixed $id_domanda_pagamento
     */
    public function setIdDomandaPagamento($id_domanda_pagamento) {
        $this->id_domanda_pagamento = $id_domanda_pagamento;
    }

    /**
     * @return mixed
     */
    public function getProgramma() {
        return $this->programma;
    }

    /**
     * @param mixed $programma
     */
    public function setProgramma($programma) {
        $this->programma = $programma;
    }

    /**
     * @return mixed
     */
    public function getFondo() {
        return $this->fondo;
    }

    /**
     * @param mixed $fondo
     */
    public function setFondo($fondo) {
        $this->fondo = $fondo;
    }

    public function __toString() {
        return $this->id_domanda_pagamento . ' - ' . $this->getProgramma()->getDescrizioneProgramma();
    }
}
