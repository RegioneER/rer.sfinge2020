<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use BaseBundle\Service\BaseService;

/**
 * Description of AGestoreTabelleContesto
 *
 * @author lfontana
 */
abstract class AGestoreTabelleContesto extends BaseService implements IGestoreTabelleContesto{
    
    const ELENCO_TABELLE_CONTESTO = 'elenco_tabelle_contesto';
    const ELENCO_TABELLE = 'dettaglio_tabelle_contesto';
    
    protected $tabella;

    public function __construct(ContainerInterface $container, \MonitoraggioBundle\Entity\ElencoTabelleContesto $tabella = null) {
        parent::__construct($container);
        $this->tabella = $tabella;
    }
    
    public static function getSuffisso($tabella){
        return is_null($tabella) ? 'Elenco' : str_replace('.','_', $tabella->getCodice());
    } 
    
    protected function istanzia($classe){
        if( class_exists($classe)){
            return new $classe($this->tabella);
        }
        throw new \Exception ('Classe non implementata');
    }
    
    public function getOggettoFormModelView(){
        return $this->istanzia($this->getClasseFormModelView());
        
    }
   
   public function getClasseFormModelView(){
       return 'MonitoraggioBundle\Form\Entity\TabelleContesto\\' . self::getSuffisso($this->tabella);
   }
   
   protected function getTwig(){
       $twig = 'MonitoraggioBundle:tabelleContesto:lista_'. self::getSuffisso($this->tabella) . '.html.twig';
       if( $this->container->get('templating')->exists($twig)){
           return $twig;
       }
       return 'MonitoraggioBundle:tabelleContesto:lista_Default.html.twig';
   }
   
   protected function getFormInsertTwig(){
        $res = 'MonitoraggioBundle:tabelleContesto:inserisci_'. self::getSuffisso($this->tabella) . '.html.twig';
        if( !$this->container->get('templating')->exists($res) ){
            return 'MonitoraggioBundle:tabelleContesto:inserisci_Default.html.twig';
        }
        return $res;
   }

    /*
     *
     */
   protected function getFormEditTwig(){
        $res = 'MonitoraggioBundle:tabelleContesto:modifica_'. self::getSuffisso($this->tabella) . '.html.twig';
        if( !$this->container->get('templating')->exists($res) ){
            return 'MonitoraggioBundle:tabelleContesto:modifica_Default.html.twig';
        }
        return $res;
   }
   
   protected function getEntity(){
       return 'MonitoraggioBundle\Entity\\' . $this->tabella->getClasseEntity();
   }
   
   protected function getEntityType(){
       return 'MonitoraggioBundle\Form\\' . $this->tabella->getClasseEntity().'Type';
   }
   
   protected function getUrlElenco(){
       if($this->tabella){
           return $this->generateUrl( self::ELENCO_TABELLE, array('tabellaId' => $this->tabella->getId()));
       }
       return $this->generateUrl( self::ELENCO_TABELLE_CONTESTO );
       
   }
   
   abstract protected function getDefaultOptions();
   
   protected function getFormViewTwig(){
        $res = 'MonitoraggioBundle:tabelleContesto:visualizza_'. self::getSuffisso($this->tabella) . '.html.twig';
        if( !$this->container->get('templating')->exists($res) ){
            return 'MonitoraggioBundle:tabelleContesto:visualizza_Default.html.twig';
        }
        return $res;
   }
}