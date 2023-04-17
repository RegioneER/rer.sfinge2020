<?php
namespace RichiesteBundle\Form\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ModificaAmbitoTematicoS3Proponente
{
	/**
	 * @Assert\NotNull(groups={"ambito_tematico_s3"})
	 */
	private $ambito_tematico_s3;
	
	/**
	 * @Assert\Count(min=1, groups={"descrittori"})
	 */
	private $descrittori;

    /**
     * @return mixed
     */
    public function getAmbitoTematicoS3()
    {
        return $this->ambito_tematico_s3;
    }

    /**
     * @param $ambito_tematico_s3
     * @return void
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
     * @return string
     */
	public function getType(): string
    {
		return "RichiesteBundle\Form\AmbitoTematicoS3ProponenteType";
	}
}
