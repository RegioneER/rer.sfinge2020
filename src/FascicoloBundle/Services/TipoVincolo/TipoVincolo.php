<?php

namespace FascicoloBundle\Services\TipoVincolo;

/**
 *
 * @author aturdo
 */
abstract class TipoVincolo implements TipoVincoloInteface {
	
	protected static $container;
	
	public function __construct($container) {
		self::$container = $container;
	}
		
}
