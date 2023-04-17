<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ValutazioneElementoChecklistControlloType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {		
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
			$valutazione_elemento = $event->getData();
			$elemento = $valutazione_elemento->getElemento();
			$form = $event->getForm();
			$opzioni = array();
			$constraints = array();
			
			switch ($elemento->getTipo()) {
				case "choice":
					$type = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
					$opzioni["choices"] = $elemento->getChoices();
					$opzioni["empty_value"] = "-";
					$data = $valutazione_elemento->getValore();
					break;
				case "integer":
					$type = 'Symfony\Component\Form\Extension\Core\Type\TextType';
					$data = $valutazione_elemento->getValore();
					$constraints[] = new \Symfony\Component\Validator\Constraints\Length(array('max' => 9));
					break;
				case "checkbox":
					$type = "Symfony\Component\Form\Extension\Core\Type\CheckboxType";
					$data = $valutazione_elemento->getValore() > 0;
					break;
				case "text":
					$type = 'Symfony\Component\Form\Extension\Core\Type\TextType';
					$data = $valutazione_elemento->getValore();
					break;
				case "textarea":
					$type = 'Symfony\Component\Form\Extension\Core\Type\TextareaType';
					$data = $valutazione_elemento->getValore();
					break;				
			}
			
			$opzioni["label"] = $elemento->getDescrizione();
			$opzioni["required"] = true;
			$opzioni["data"] = $data;
			$opzioni["constraints"] = $constraints;
			
			$form->add('valore', $type, $opzioni);

			if ($elemento->getSezioneChecklist()->getCommento()) {
				$form->add('commento', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', array("required" => false));
			}
            
            if ($elemento->getSezioneChecklist()->getDocumentiBool()) {
				$form->add('documenti_text', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', array("required" => false));
			}
            
            if ($elemento->getSezioneChecklist()->getCollocazioneBool()) {
				$form->add('collocazione', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', array("required" => false));
			}
            
             if ($elemento->getSezioneChecklist()->getCollocazioneBenBool()) {
				$form->add('collocazione_ben', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', array("required" => false));
			}
		});		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ValutazioneElementoChecklistControllo',
		));
	}

}
