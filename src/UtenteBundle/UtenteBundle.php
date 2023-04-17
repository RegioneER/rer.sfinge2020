<?php

namespace UtenteBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UtenteBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
