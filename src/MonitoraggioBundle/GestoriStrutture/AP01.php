<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 03/07/17
 * Time: 17:42
 */

namespace MonitoraggioBundle\GestoriStrutture;

use MonitoraggioBundle\Service\GestoreStruttureBase;

class AP01 extends GestoreStruttureBase
{
    public function getElenco(array $formOptions = array())
    {
        $em = $this->getEm();

        return parent::getElenco(array_merge($formOptions, array(
            'procedure' => $em->getRepository('MonitoraggioBundle:TC1ProceduraAttivazione')->findAll(),
        )));
    }
}
