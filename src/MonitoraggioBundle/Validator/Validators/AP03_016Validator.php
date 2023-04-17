<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP03_016Validator extends AP03_014Validator
{
    public function __construct(\Doctrine\ORM\EntityManagerInterface $em)
    {
        parent::__construct($em);
        $this->FONDO = 'YEI';
        $this->CLASSIFICAZIONI = array(
            'CI',
            'FF',
            'TT',
            'MET',
            'AE',
            'DTS',
        );
    }
}