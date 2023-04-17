<?php

namespace FascicoloBundle\Services\TipoCampo;

/**
 *
 * @author aturdo
 */
interface TipoCampoInteface {
	public function getType();
	
	public function validate($campo, $istanzeCampo, $checkRequired);
	
	public function getTypeOptions($campo, $dato);
	
	public function getTypeData($campo, $dato);
	
	public function calcolaValoreRaw($campo, $valore);
	
}
