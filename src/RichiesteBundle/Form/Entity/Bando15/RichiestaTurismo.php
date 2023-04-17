<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace RichiesteBundle\Form\Entity\Bando15;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaTurismo extends Richiesta
{
	/**
	 * @Assert\NotBlank()
	 */
	protected $misura;

	/**
	 * @Assert\NotBlank()
	 */    
	protected $regime;


	protected $multi_proponente;

	function getMisura() {
		return $this->misura;
	}

	function getRegime() {
		return $this->regime;
	}

	function setMisura($misura) {
		$this->misura = $misura;
	}

	function setRegime($regime) {
		$this->regime = $regime;
	}

		/**
	 * @return mixed
	 */
	public function getMultiProponente()
	{
		return $this->multi_proponente;
	}

	/**
	 * @param mixed $multi_proponente
	 */
	public function setMultiProponente($multi_proponente)
	{
		$this->multi_proponente = $multi_proponente;
	}



}