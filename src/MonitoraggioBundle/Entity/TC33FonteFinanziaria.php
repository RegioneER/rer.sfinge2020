<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:33
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC33FonteFinanziariaRepository")
 * @ORM\Table(name="tc33_fonte_finanziaria")
 */
class TC33FonteFinanziaria extends EntityLoggabileCancellabile {
    use Id;

    const FESR = 'ERDF';
    const STATO = 'FDR';
    const REGIONE = 'FPREG';
    const PRIVATO = 'PRT';
    const COMUNE = 'FPCOM';
    const ALTRO_PUBBLICO = 'SSA';

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=10, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_fondo;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Length(max=100, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_fondo;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     * @Assert\Length(max=2, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_fonte;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     * @Assert\Length(max=250, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_fonte;

    public function __construct(?string $fondo = null) {
        $this->cod_fondo = $fondo;
    }

    public function getCodFondo(): ?string {
        return $this->cod_fondo;
    }

    public function setCodFondo(?string $cod_fondo): self {
        $this->cod_fondo = $cod_fondo;

        return $this;
    }

    public function getDescrizioneFondo(): ?string {
        return $this->descrizione_fondo;
    }

    public function setDescrizioneFondo(?string $descrizione_fondo) {
        $this->descrizione_fondo = $descrizione_fondo;

        return $this;
    }

    public function getCodFonte(): ?string {
        return $this->cod_fonte;
    }

    public function setCodFonte(?string $cod_fonte): self {
        $this->cod_fonte = $cod_fonte;

        return $this;
    }

    public function getDescrizioneFonte(): ?string {
        return $this->descrizione_fonte;
    }

    public function setDescrizioneFonte(?string $descrizione_fonte): self {
        $this->descrizione_fonte = $descrizione_fonte;

        return $this;
    }

    public function __toString() {
        return $this->cod_fondo . ' - ' . $this->descrizione_fondo;
    }
}
