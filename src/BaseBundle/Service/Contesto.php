<?php

namespace BaseBundle\Service;

class Contesto
{

    public function getContestoRisorsa($risorsa, $contesto)
	{
		$metodo = "get". ucfirst($contesto);
		if (method_exists($risorsa, $metodo)) 
		{
			return $risorsa->$metodo();
		} else {
			throw new \Exception("Metodo $metodo non trovato nella classe ".get_class($risorsa));
		}
    }
}