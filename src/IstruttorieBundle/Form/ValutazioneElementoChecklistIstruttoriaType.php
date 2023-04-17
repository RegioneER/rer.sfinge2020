<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use IstruttorieBundle\Entity\ValutazioneElementoChecklistIstruttoria;

class ValutazioneElementoChecklistIstruttoriaType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var ValutazioneElementoChecklistIstruttoria $valutazione_elemento */
            $valutazione_elemento = $event->getData();
            $elemento = $valutazione_elemento->getElemento();
            $form = $event->getForm();
            $opzioni = array();
            $constraints = array();
            $data = null;
            $type = null;

            switch ($elemento->getTipo()) {
                case "choice":
                    $type = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
                    $opzioni['choices_as_values'] = true;
                    $opzioni["choices"] = \array_flip($elemento->getChoices());
                    $opzioni["placeholder"] = "-";
                    $data = $valutazione_elemento->getValore();
                    break;
                case "integer":
                    $type = self::integer;
                    $data = $valutazione_elemento->getValore();
                    $constraints[] = new \Symfony\Component\Validator\Constraints\Length(array('max' => 10));
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
                case "importo":
                    $type = 'BaseBundle\Form\ImportoType';
                    $opzioni["currency"] = "EUR";
                    $opzioni["grouping"] = true;
                    $data = $valutazione_elemento->getValore();
                    break;
                case "datetime":
                    $type = 'Symfony\Component\Form\Extension\Core\Type\DateTimeType';
                    $opzioni["widget"] = 'single_text';
                    $opzioni["input"] = 'datetime';
                    $opzioni["format"] = 'dd/MM/yyyy HH:mm';
                    $data = $valutazione_elemento->getValore() == null ? null : new \DateTime($valutazione_elemento->getValore());
                    break;
                case "date":
                    $type = 'Symfony\Component\Form\Extension\Core\Type\BirthdayType';
                    $opzioni["widget"] = 'single_text';
                    $opzioni["input"] = 'datetime';
                    $opzioni["format"] = 'dd/MM/yyyy';
                    $data = $valutazione_elemento->getValore() == null ? null : new \DateTime($valutazione_elemento->getValore());
                    break;
                case "decimal":
                    $type = 'Symfony\Component\Form\Extension\Core\Type\TextType';
                    $decimal = is_null($elemento->getLunghezzaMassima()) ? "2" : $elemento->getLunghezzaMassima();
                    $constraints[] = new \Symfony\Component\Validator\Constraints\Regex(array('pattern' => "/^-?(0|[1-9]\d{0,9})(,\d{1,$decimal})?$/", 'message' => "Il valore inserito non è un numero decimale o supera il range consentito"));
                    $data = $valutazione_elemento->getValore();
                    break;
            }

            $opzioni["label"] = $elemento->getDescrizione();
            $opzioni["required"] = $elemento->getOpzionale() != true;
            $opzioni["data"] = $data;
            $opzioni["constraints"] = $constraints;
            $opzioni['attr'] = [
                'data-codice' => $valutazione_elemento->getCodiceElemento() ?: false,
            ];

            $form->add('valore', $type, $opzioni);

            if ($elemento->getSezioneChecklist()->getCommento()) {
                $form->add('commento', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', array("required" => false));
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => ValutazioneElementoChecklistIstruttoria::class,
        ));
    }

}
