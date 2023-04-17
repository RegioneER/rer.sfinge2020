<?php

namespace FascicoloBundle\Services\TipoVincolo;

/**
 *
 * @author aturdo
 */
interface TipoVincoloInteface {
	
	public function addTypeParameters($builder);
	
	public function getParametersFields();
	
	public function validate($vincolo, $istanzeCampo);
	
	public function validaVincolo($vincolo, $form);
}
