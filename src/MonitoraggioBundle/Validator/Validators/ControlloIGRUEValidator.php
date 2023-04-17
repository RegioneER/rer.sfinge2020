<?php

namespace MonitoraggioBundle\Validator\Validators;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use RichiesteBundle\Entity\Richiesta;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class ControlloIGRUEValidator extends AbstractValidator
{
    const CONTROLLI = [
        '001' => [ 
            'messaggio' => 'sfinge.monitoraggio.ap03_001',
            'strutture' => ['AP03'],
        ]
    ];

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var array
     */
    protected $controlli;

    public function __construct(EntityManagerInterface $em, array $controlli)
    {
        $this->em = $em;
        $this->controlli = $controlli;
    }

    /**
     * @param Richiesta $value
     */
    public function validate($value,  Constraint $constraint){
        foreach ($this->controlli as $codice => $controllo) {
            $valido = $this->verifica($value, $codice);
            if(! $valido){
                $this->context->buildViolation($controllo['messaggio'],[
                    'strutture' => \implode(', ', $controllo['strutture']),
                    'codice' => $codice,
                ])
                    ->addViolation();
            }
        }
    }

    protected function verifica(Richiesta $richiesta, string $controllo):bool{
        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addScalarResult('res', 'res');
        $res = $this->em->createNativeQuery("SELECT controllo_igrue(:richiesta_id, :controllo) as res ", $rsm)
                ->setParameter('richiesta_id', $richiesta->getId())
                ->setParameter('controllo', $controllo)
                ->getSingleScalarResult();
        
        return $res == 1;
    }

}