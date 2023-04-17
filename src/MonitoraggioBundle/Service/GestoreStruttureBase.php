<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 23/06/17
 * Time: 15:58
 */


namespace MonitoraggioBundle\Service;

class GestoreStruttureBase extends AGestoreStrutture
{

    public function getElenco(array $formOptions = array())
    {
        $datiRicerca = $this->getOggettoFormModelView();
        $options = array_merge(array(
            'data_class' => get_class($datiRicerca),
        ), $formOptions
        );

        $risultato = array_merge_recursive(
            $this->container->get("ricerca")->ricerca($datiRicerca, $options),
            array(
                'tabella' => $this->struttura,
                'colonne' => $this->getColonne($datiRicerca),
            )
        );

        return $this->render($this->getTwig(), $risultato);
    }


    public function getElencoStrutture() {
        $datiRicerca = $this->getOggettoFormModelView();

        $options = array(
            'data_class' => get_class($datiRicerca),
        );

        $risultato = array_merge_recursive(
            $this->container->get("ricerca")->ricerca($datiRicerca, $options),
            array('tabella' => $this->struttura,
                'colonne' => $this->getColonne($datiRicerca),
            )
        );

        return $this->render($this->getTwig(), $risultato);
    }


    protected function getColonne( $modelView){
        $reflection = new \ReflectionObject($modelView);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
        $reader =  new \Doctrine\Common\Annotations\AnnotationReader();
        $annotations = array_filter(array_map( function($property) use ($reader){
            $res= $reader->getPropertyAnnotation($property, 'MonitoraggioBundle\Annotations\ViewElenco');
            if (!\is_null($res)) {
                if (\is_null($res->property)) {
                    $res->property = str_replace('_', '', $property->name);
                }
                else{
                    $res->property = str_replace('_', '', $res->property);
                }
            }
            return $res;
        }, $properties), function ($el){
            //Array filter
            return !is_null($el) && $el->show;
        });
        usort($annotations, function($el1, $el2){
            if($el1->ordine == $el2->ordine){
                return 0;
            }
            return ($el1->ordine < $el2->ordine) ? -1 : 1;
        });
        return $annotations;
    }


    public function pulisciElenco() {
        $this->container->get("ricerca")->pulisci($this->getOggettoFormModelView());
        return $this->redirect( $this->getUrlElenco() );
    }

    public function inserisciElemento(array $formOptions = array(), array $twigOptions = array() ) {
        
        $em = $this->getEm();
        $entity = $this->getEntity();
        $res = new $entity();
        $request = $this->getCurrentRequest();
        $formOptions = array_merge_recursive(array(
            'url_indietro' =>  $this->getUrlElenco(),
            'data_class' => $this->getEntity(),
        ), $formOptions);
        $form = $this->createForm($this->getEntityType(), $res, $formOptions);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                //Effettua salvataggio informazioni
                $em->persist($res);
                $em->flush();
                unset($res);
                $entity = new $entity();
                $form = $this->createForm($this->getEntityType(), $entity, $formOptions);
            }
        }
        $options = array_merge(array(
            'form' => $form->createView(),
            'tabella' => $this->struttura,
        ), $twigOptions);
        return $this->render($this->getFormInsertTwig(), $options);
        
    }

    public function modificaElemento($recordId, array $formOptions = array(), array $twigOptions = array() ){
        
        $em = $this->getEm();
        $res = $em->getRepository( $this->getEntity() )->find($recordId);
        if (!$res ){
            return $this->addErrorRedirect("Il record non è stato trovato", "elenco_tabelle_contesto");
        }
        $request = $this->getCurrentRequest();
        $formOptions = array_merge_recursive(array(
            'url_indietro' => $this->generateUrl( self::DETTAGLIO_STRUTTURE_PROTOCOLLO, array('tabellaId' => $this->struttura->getId() )),
            'data_class' => $this->getEntity(),
            'disabled' => false,
        ), $formOptions);
        $form = $this->createForm($this->getEntityType(), $res, $formOptions);
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                //Effettua salvataggio informazioni
                $em->persist($res);
                $em->flush();
            }
        }
        $options = array_merge(array(
            'form' => $form->createView(),
            'tabella' => $this->struttura,
        ), $twigOptions);
        return $this->render($this->getFormEditTwig(), $options);
    }

    public function visualizzaElemento($recordId, array $formOptions = array(), array $twigOptions = array() ){

        
        $em = $this->getEm();
        $res = $em->getRepository( $this->getEntity() )->find($recordId);
        if (!$res ){
            return $this->addErrorRedirect("Il record non è stato trovato", "elenco_tabelle_contesto");
        }
        $request = $this->getCurrentRequest();
        $formOptions = array_merge_recursive(array(
            'url_indietro' => $this->generateUrl( self::DETTAGLIO_STRUTTURE_PROTOCOLLO, array('strutturaId' => $this->struttura->getId() )),
            'data_class' => $this->getEntity(),
            'disabled' => true,
        ), $formOptions);
        $form = $this->createForm($this->getEntityType(), $res, $formOptions);

        $options = array_merge(array(
            'form' => $form->createView(),
            'tabella' => $this->struttura,
        ), $twigOptions);

        return $this->render($this->getFormEditTwig(), $options);
    }


    /**
     *
     * @return array
     */
    protected function getDefaultOptions(){
        return array();
    }

}
