<?php

namespace BaseBundle\Form;

use Symfony\Component\Form\AbstractType;

class RicercaType extends CommonType {
	
	protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}

}
