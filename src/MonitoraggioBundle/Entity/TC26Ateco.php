<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:25
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC26AtecoRepository")
 * @ORM\Table(name="tc26_ateco")
 */
class TC26Ateco extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=120, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_ateco_anno;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     * @Assert\Length(max=1000, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_codice_ateco;

    /**
     * @ORM\ManyToOne(targetEntity="SoggettoBundle\Entity\Ateco")
     * @ORM\JoinColumn(name="sfinge_ateco", referencedColumnName="id")
     */
    protected $sfingeAteco;

    /**
     * @return mixed
     */
    public function getCodAtecoAnno() {
        return $this->cod_ateco_anno;
    }

    /**
     * @param mixed $cod_ateco_anno
     */
    public function setCodAtecoAnno($cod_ateco_anno) {
        $this->cod_ateco_anno = $cod_ateco_anno;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneCodiceAteco() {
        return $this->descrizione_codice_ateco;
    }

    /**
     * @param mixed $descrizione_codice_ateco
     */
    public function setDescrizioneCodiceAteco($descrizione_codice_ateco) {
        $this->descrizione_codice_ateco = $descrizione_codice_ateco;
    }

    public function __toString() {
        return $this->cod_ateco_anno . ' - ' . $this->descrizione_codice_ateco;
    }
}
