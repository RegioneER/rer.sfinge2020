<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @author vbuscemi
 */
class Esporta {
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * @param bool $NNotNull Se true allora ritorna N in caso di false, altrimenti NULL
     * @param bool $flag     boolean da convertire in S/N o S/NULL
     *
     * @return string
     */
    protected static function bool2SN($flag, $NNotNull = false) {
        return $flag ? 'S' : ($NNotNull ? 'N' : null);
    }

    protected function createFromFormatV2($string_date) {
        if (empty($string_date)) {
            return null;
        }
        $return = \DateTime::createFromFormat('d/m/Y h:i:s', $string_date . ' 00:00:00');
        return $return ? $return : null;
    }

    /**
     * @param string $string
     * @return float
     */
    public static function convertNumberFromString($string) {
        $p1 = str_replace(".", "", $string);
        $p2 = str_replace(",", ".", $p1);
        return \floatval($p2);
    }
}
