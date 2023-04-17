<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 05/01/16
 * Time: 16:09
 */

namespace RichiesteBundle\Form\Entity\Bando3;

use BaseBundle\Service\AttributiRicerca;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Constraints as Assert;

class RichiestaExportImprese extends Richiesta
{
	protected $multi_proponente;

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