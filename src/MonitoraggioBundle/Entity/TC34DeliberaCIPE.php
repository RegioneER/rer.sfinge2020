<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:35
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC34DeliberaCIPERepository")
 * @ORM\Table(name="tc34_delibera_cipe")
 */
class TC34DeliberaCIPE extends EntityLoggabileCancellabile {
    use Id;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $cod_del_cipe;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $numero;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Length(max=4, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $anno;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Assert\Length(max=5, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $tipo_quota;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     * @Assert\Length(max=200, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     */
    protected $descrizione_quota;

    /**
     * @return mixed
     */
    public function getCodDelCipe() {
        return $this->cod_del_cipe;
    }

    /**
     * @param mixed $cod_del_cipe
     */
    public function setCodDelCipe($cod_del_cipe) {
        $this->cod_del_cipe = $cod_del_cipe;
    }

    /**
     * @return mixed
     */
    public function getNumero() {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero) {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getAnno() {
        return $this->anno;
    }

    /**
     * @param mixed $anno
     */
    public function setAnno($anno) {
        $this->anno = $anno;
    }

    /**
     * @return mixed
     */
    public function getTipoQuota() {
        return $this->tipo_quota;
    }

    /**
     * @param mixed $tipo_quota
     */
    public function setTipoQuota($tipo_quota) {
        $this->tipo_quota = $tipo_quota;
    }

    /**
     * @return mixed
     */
    public function getDescrizioneQuota() {
        return $this->descrizione_quota;
    }

    /**
     * @param mixed $descrizione_quota
     */
    public function setDescrizioneQuota($descrizione_quota) {
        $this->descrizione_quota = $descrizione_quota;
    }

    public function __toString() {
        return $this->descrizione_quota . ' N° ' . $this->numero . '/' . $this->anno;
    }
}
