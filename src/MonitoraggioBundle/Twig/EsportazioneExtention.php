<?php
/**
 * @author lfontana
 */
namespace MonitoraggioBundle\Twig;

use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use Doctrine\ORM\EntityManager;



class EsportazioneExtention extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('struttura', array($this, 'getStruttura')),
            new \Twig_SimpleFilter('strutturaId', array($this, 'getIDStruttura')),
        );
    }

    public function getStruttura(MonitoraggioConfigurazioneEsportazioneTavole $tavola)
    {
        $res = $this->em->getRepository('MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole')->findStruttureByTavola($tavola);
        return $res;
    }

    public function getName()
    {
        return 'monitoraggio';
    }

    public function getIDStruttura($struttura)
    {
        $reflObj = new \ReflectionObject($struttura);        
        $definizioneStruttura = $this->em
        ->getRepository('MonitoraggioBundle:ElencoStruttureProtocollo')
        ->findOneByClasseEntity($reflObj->getShortName());
        return \is_null($definizioneStruttura) ? NULL : $definizioneStruttura->getId();
    }
}