<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 30/06/17
 * Time: 09:50
 */


namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Service\BaseService;
use BaseBundle\Controller\BaseController;

/**
 * Description of AGestoreStrutture
 *
 * @author lfontana
 */
abstract class AGestoreStrutture extends BaseService implements IGestoreStrutture{

    const ELENCO_STRUTTURE_PROTOCOLLO = 'elenco_strutture_protocollo';
    const DETTAGLIO_STRUTTURE_PROTOCOLLO = 'dettaglio_strutture_protocollo';

    protected $struttura;

    public function __construct(ContainerInterface $container, \MonitoraggioBundle\Entity\ElencoStruttureProtocollo $struttura = null) {
        parent::__construct($container);
        $this->struttura = $struttura;
    }

    /*
     * AP00, AP01, ecc...
     */
   public static function getSuffisso($tabella){
        return is_null($tabella) ? 'RicercaStruttura' : str_replace('.','_', $tabella->getCodice());
    } 
    
    protected function istanzia($classe){
        if( class_exists($classe)){
            return new $classe($this->struttura);
        }
        throw new \Exception ('Classe non implementata: '.$classe);
    }

    /*
     * Oggetto Ricerca nulla pagina ('RicercaStruttura.php, AP01.php', ecc...)
     */
    public function getOggettoFormModelView(){
        return $this->istanzia($this->getClasseFormModelView());

    }

    /*
     * Form Ricerca - Classe (AP01, AP02)
     */
    public function getClasseFormModelView(){
        return 'MonitoraggioBundle\Form\Entity\Strutture\\' . self::getSuffisso($this->struttura);
    }


    /*
     * Nome file - Twig di visualizzazione (struttura_AP01, struttura_AP02, ecc....)
     */
    protected function getTwig(){
        $twig = 'MonitoraggioBundle:Strutture:struttura_'. self::getSuffisso($this->struttura) . '.html.twig';
        if( $this->container->get('templating')->exists($twig)){
            return $twig;
        }
        return 'MonitoraggioBundle:Strutture:struttura_Default.html.twig';
    }

    protected function getFormInsertTwig(){
        $res = 'MonitoraggioBundle:Strutture:inserisci_'. self::getSuffisso($this->struttura) . '.html.twig';
        if( !$this->container->get('templating')->exists($res) ){
            return 'MonitoraggioBundle:Strutture:inserisci_Default.html.twig';
        }
        return $res;
    }

    /*
     *
     */
    protected function getFormEditTwig(){
        $res = 'MonitoraggioBundle:Strutture:modifica_'. self::getSuffisso($this->struttura) . '.html.twig';
        if( !$this->container->get('templating')->exists($res) ){
            return 'MonitoraggioBundle:Strutture:modifica_Default.html.twig';
        }
        return $res;
    }

    /*
     * Restituisce la classe dell'Entity
     */
    protected function getEntity(){
        return 'MonitoraggioBundle\Entity\\' . $this->struttura->getClasseEntity();
    }

    /*
     * * Restituisce la classe del Type dell'Entity
     */
    protected function getEntityType(){
        return 'MonitoraggioBundle\Form\\' . $this->struttura->getClasseEntity().'Type';
    }

    protected function getUrlElenco(){
        if($this->struttura){
                    return $this->generateUrl(self::DETTAGLIO_STRUTTURE_PROTOCOLLO, array('strutturaId' => $this->struttura->getId()));
        }
        return $this->generateUrl( self::ELENCO_STRUTTURE_PROTOCOLLO );

    }
}


















