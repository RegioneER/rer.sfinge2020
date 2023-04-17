<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:18
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC21QualificaRepository")
 * @ORM\Table(name="tc21_qualifica")
 */
class TC21Qualifica extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=15, nullable=false)
     * @Assert\NotNull
     * @Assert\Length(max=15, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_qualifica;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_qualifica;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     * @Assert\NotNull
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_amministrazione;

    /**
     * @return mixed
     */
    public function getCodQualifica() {
        return $this->cod_qualifica;
    }

    /**
     * @param mixed $cod_qualifica
     */
    public function setCodQualifica($cod_qualifica) {
        $this->cod_qualifica = $cod_qualifica;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneQualifica() {
        return $this->descrizione_qualifica;
    }

    /**
     * @param mixed $descrizione_qualifica
     */
    public function setDescrizioneQualifica($descrizione_qualifica) {
        $this->descrizione_qualifica = $descrizione_qualifica;
    }

    /**
     * @return mixed
     */
    public function getCodAmministrazione() {
        return $this->cod_amministrazione;
    }

    /**
     * @param mixed $cod_amministrazione
     */
    public function setCodAmministrazione($cod_amministrazione) {
        $this->cod_amministrazione = $cod_amministrazione;
    }
}
