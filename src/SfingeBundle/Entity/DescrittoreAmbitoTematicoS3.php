<?php
namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * DescrittoreAmbitoTematicoS3
 *
 * @ORM\Table(name="descrittori_ambiti_tematici_s3")
 * @ORM\Entity()
 */
class DescrittoreAmbitoTematicoS3 extends EntityTipo
{
    /**
     * @return string
     */
	function __toString()
    {
		return $this->getDescrizione();
	}

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\AmbitoTematicoS3", inversedBy="descrittori")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $ambito_tematico_s3;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente", mappedBy="descrittore")
     */
    protected $descrittore_proponente;

    /**
     * @return mixed
     */
    public function getAmbitoTematicoS3()
    {
        return $this->ambito_tematico_s3;
    }

    /**
     * @param mixed $ambito_tematico_s3
     */
    public function setAmbitoTematicoS3($ambito_tematico_s3): void
    {
        $this->ambito_tematico_s3 = $ambito_tematico_s3;
    }

    /**
     * @return mixed
     */
    public function getDescrittoreProponente()
    {
        return $this->descrittore_proponente;
    }

    /**
     * @param mixed $descrittore_proponente
     */
    public function setDescrittoreProponente($descrittore_proponente): void
    {
        $this->descrittore_proponente = $descrittore_proponente;
    }
}
