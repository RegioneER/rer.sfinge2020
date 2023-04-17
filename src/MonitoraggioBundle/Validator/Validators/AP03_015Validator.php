<?php
namespace MonitoraggioBundle\Validator\Validators;



class AP03_015Validator extends AP03_014Validator
{
    protected static $PROGRAMMI = array(
        '2014IT05SFOP004',
        '2014IT05SFOP005',
        '2014IT05SFOP006',
        '2014IT05SFOP007',
        '2014IT05SFOP008',
        '2014IT05SFOP008',
        '2014IT05SFOP009',
        '2014IT05SFOP010',
        '2014IT05SFOP011',
        '2014IT05SFOP012',
        '2014IT05SFOP013',
        '2014IT05SFOP014',
        '2014IT05SFOP015',
        '2014IT05SFOP016',
        '2014IT05SFOP020',
        '2014IT05SFOP021',
        '2014IT16M2OP001',
        '2014IT16M2OP002',
        '2014IT16M2OP006',
    );

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em)
    {
        parent::__construct($em);
        $this->FONDO = 'ESF';
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