<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ValutazioneElementoChecklistPagamentoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $valutazione_elemento = $event->getData();
            $elemento = $valutazione_elemento->getElemento();
            $form = $event->getForm();
            $opzioni = array();
            $constraints = array();

            switch ($elemento->getTipo()) {
                case "choice":
                    $type = ChoiceType::class;
                    $opzioni['choices_as_values'] = true;
                    $opzioni["choices"] = \array_flip($elemento->getChoices());
                    $opzioni["placeholder"] = "-";
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
                    $data = null == $valutazione_elemento->getValore() ? null : new \DateTime($valutazione_elemento->getValore());
                    break;
                case "date":
                    $type = 'Symfony\Component\Form\Extension\Core\Type\BirthdayType';
                    $opzioni["widget"] = 'single_text';
                    $opzioni["input"] = 'datetime';
                    $opzioni["format"] = 'dd/MM/yyyy';
                    $data = null == $valutazione_elemento->getValore() ? null : new \DateTime($valutazione_elemento->getValore());
                    break;
            }

            $opzioni["label"] = $elemento->getDescrizione();
            $opzioni["required"] = is_null($elemento->getOpzionale()) ? true : !$elemento->getOpzionale();
            $opzioni["data"] = $data;
            $opzioni["constraints"] = $constraints;

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
            'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneElementoChecklistPagamento',
        ));
    }
}
