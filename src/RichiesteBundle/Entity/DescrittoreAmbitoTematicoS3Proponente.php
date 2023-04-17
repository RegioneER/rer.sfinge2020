<?php
namespace RichiesteBundle\Entity;

use BaseBundle\Validator\Constraints\ValidaLunghezza;
use Doctrine\ORM\Mapping as ORM;

/**
 * DescrittoreAmbitoTematicoS3Proponente
 *
 * @ORM\Table(name="descrittori_ambiti_tematici_s3_proponenti")
 * @ORM\Entity()
 */
class DescrittoreAmbitoTematicoS3Proponente
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\AmbitoTematicoS3Proponente", inversedBy="descrittori")
     * @ORM\JoinColumn(name="ambitotematicos3proponente_id", referencedColumnName="id", nullable=false)
     */
    protected $ambito_tematico_s3_proponente;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\DescrittoreAmbitoTematicoS3", inversedBy="descrittori")
     * @ORM\JoinColumn(name="descrittoreambitotematicos3_id", referencedColumnName="id", nullable=false)
     */
    protected $descrittore;

    /**
     * @ORM\Column(name="descrizione", type="text", nullable=true)
     * @ValidaLunghezza(min=5, max=3000, groups={"Default"})
     */
    protected $descrizione;


    /**
     * @return mixed
     */
    public function getAmbitoTematicoS3Proponente()
    {
        return $this->ambito_tematico_s3_proponente;
    }

    /**
     * @param mixed $ambito_tematico_s3_proponente
     */
    public function setAmbitoTematicoS3Proponente($ambito_tematico_s3_proponente): void
    {
        $this->ambito_tematico_s3_proponente = $ambito_tematico_s3_proponente;
    }

    /**
     * @return mixed
     */
    public function getDescrittore()
    {
        return $this->descrittore;
    }

    /**
     * @param mixed $descrittore
     */
    public function setDescrittore($descrittore): void
    {
        $this->descrittore = $descrittore;
    }

    /**
     * @return mixed
     */
    public function getDescrizione()
    {
        return $this->descrizione;
    }

    /**
     * @param mixed $descrizione
     */
    public function setDescrizione($descrizione): void
    {
        $this->descrizione = $descrizione;
    }

    public function __toString()
    {
        return $this->getDescrittore()->getDescrizione();
    }
}
