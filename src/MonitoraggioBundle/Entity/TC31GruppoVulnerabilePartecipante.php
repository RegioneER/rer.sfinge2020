<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:31
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC31GruppoVulnerabilePartecipanteRepository")
 * @ORM\Table(name="tc31_gruppo_vulnerabile_partecipante")
 */
class TC31GruppoVulnerabilePartecipante extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $codice_vulnerabile_pa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descr_vulnerabile_pa;

    /**
     * @return mixed
     */
    public function getCodiceVulnerabilePa() {
        return $this->codice_vulnerabile_pa;
    }

    /**
     * @param mixed $codice_vulnerabile_pa
     */
    public function setCodiceVulnerabilePa($codice_vulnerabile_pa) {
        $this->codice_vulnerabile_pa = $codice_vulnerabile_pa;
    }

    /**
     * @return mixed
     */
    public function getDescrVulnerabilePa() {
        return $this->descr_vulnerabile_pa;
    }

    /**
     * @param mixed $descr_vulnerabile_pa
     */
    public function setDescrVulnerabilePa($descr_vulnerabile_pa) {
        $this->descr_vulnerabile_pa = $descr_vulnerabile_pa;
    }
}
