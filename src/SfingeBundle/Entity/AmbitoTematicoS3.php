<?php
namespace SfingeBundle\Entity;

use BaseBundle\Entity\EntityTipo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AmbitoTematico
 *
 * @ORM\Table(name="ambiti_tematici_s3")
 * @ORM\Entity()
 */
class AmbitoTematicoS3 extends EntityTipo
{
    const PRIMI_TRE_AMBITI_S3 = [
        'Energia pulita, sicura e accessibile',
        'Circular Economy',
        'Clima e Risorse Naturali (aria, acqua e territorio)',
    ];

    /**
     * @return string
     */
	function __toString()
    {
		return $this->getDescrizione();
	}

    /**
     * @ORM\OneToMany(targetEntity="SfingeBundle\Entity\DescrittoreAmbitoTematicoS3", mappedBy="ambito_tematico_s3")
     */
    protected $descrittori;

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
     * @return bool
     */
    public function isPrimiTreAmbitiTematiciS3(): bool
    {
        return in_array($this->getDescrizione(), self::PRIMI_TRE_AMBITI_S3);
    }
}
