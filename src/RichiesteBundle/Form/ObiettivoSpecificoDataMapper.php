<?php

namespace RichiesteBundle\Form;

use RichiesteBundle\Entity\ObiettivoRealizzativo;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormInterface;

class ObiettivoSpecificoDataMapper extends PropertyPathMapper
{
    
    /**
     * Maps the data of a list of forms into the properties of some data.
     *
     * @param FormInterface[]|\Traversable $forms A list of {@link FormInterface} instances
     * @param ObiettivoRealizzativo $obiettivo  Structured data
     *
     * @throws UnexpectedTypeException if the type of the data parameter is not supported
     */
    public function mapFormsToData($forms, &$obiettivo){
        parent::mapFormsToData($forms, $obiettivo);
        $form = iterator_to_array($forms);

        $tipologia = $form['tipologia']->getData();
        $percentualeSS = null;
        $percentualeRI = null;
        if($tipologia == 'SS'){
            $percentualeSS = 100;
            $percentualeRI = 0;
        }
        else if($tipologia == 'RI' ){
            $percentualeSS = 0;
            $percentualeRI = 100;
        }
        $obiettivo->setPercentualeRi($percentualeRI);
        $obiettivo->setPercentualeSs($percentualeSS);
    }


    /**
     * Maps properties of some data to a list of forms.
     *
     * @param ObiettivoRealizzativo $obiettivo  Structured data
     * @param FormInterface[]|\Traversable $forms A list of {@link FormInterface} instances
     *
     * @throws UnexpectedTypeException if the type of the data parameter is not supported
     */
    public function mapDataToForms($obiettivo, $forms){
        parent::mapDataToForms($obiettivo, $forms);
        $form = iterator_to_array($forms);

        $tipologia = null;
        if($obiettivo->getPercentualeRi() == 100 ){
            $tipologia = 'RI';
        }
        else if($obiettivo->getPercentualeSs() == 100){
            $tipologia = 'SS';
        }

        $form['tipologia']->setData($tipologia);
    }
}