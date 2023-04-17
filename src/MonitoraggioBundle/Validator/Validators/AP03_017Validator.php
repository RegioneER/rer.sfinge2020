<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP03_017Validator extends AP03_014Validator
{

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em)
    {
        parent::__construct($em);
        $this->FONDO = 'EAFRD';
        $this->CLASSIFICAZIONI = array(
            'TI',
        );
    }
}