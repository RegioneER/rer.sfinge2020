<?php
namespace RichiesteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ambiti_tematici_s3_proponenti")
 */
class AmbitoTematicoS3Proponente
{
    /**
     * @ORM\Id
     * @ORM\Column(type="bigint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
	
    /**
     * @ORM\ManyToOne(targetEntity="RichiesteBundle\Entity\Proponente", inversedBy="ambiti_tematici_s3")
     * @ORM\JoinColumn(nullable=false)
     */
    private $proponente;	

    /**
     * @ORM\ManyToOne(targetEntity="SfingeBundle\Entity\AmbitoTematicoS3")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $ambito_tematico_s3;

    /**
     * @ORM\OneToMany(targetEntity="RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente", mappedBy="ambito_tematico_s3_proponente")
     */
    protected $descrittori;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getProponente()
    {
        return $this->proponente;
    }

    /**
     * @param mixed $proponente
     */
    public function setProponente($proponente): void
    {
        $this->proponente = $proponente;
    }

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
    public function getDescrittori()
    {
        return $this->descrittori;
    }

    /**
     * @param mixed $descrittori
     */
    public function setDescrittori($descrittori): void
    {
        $this->descrittori = $descrittori;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->getAmbitoTematicoS3()->getDescrizione();
    }
}
