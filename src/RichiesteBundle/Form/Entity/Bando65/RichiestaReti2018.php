<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace RichiesteBundle\Form\Entity\Bando65;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaReti2018 extends Richiesta
{
	/**
	 * @Assert\NotBlank()
	 */
	protected $tipologia;
	
	public function getTipologia() {
		return $this->tipologia;
	}

	public function setTipologia($tipologia) {
		$this->tipologia = $tipologia;
	}





}