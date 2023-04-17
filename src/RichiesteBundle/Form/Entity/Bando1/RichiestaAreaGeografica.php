<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace RichiesteBundle\Form\Entity\Bando1;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaAreaGeografica extends Richiesta
{
	/**
	 * @Assert\NotBlank()
	 */
	protected $descrizione;

	/**
	 * @Assert\NotBlank()
	 */
	protected $tipologia;

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
	public function setDescrizione($descrizione)
	{
		$this->descrizione = $descrizione;
	}

	/**
	 * @return mixed
	 */
	public function getTipologia()
	{
		return $this->tipologia;
	}

	/**
	 * @param mixed $tipologia
	 */
	public function setTipologia($tipologia)
	{
		$this->tipologia = $tipologia;
	}


}