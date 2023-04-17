<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RichiestaImpegniType extends CommonType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData();
        $tipo_impegno = $data->getTipologiaImpegno();
        $tipo_impegno = $tipo_impegno[0];
        
        $builder->add('tipologia_impegno', self::choice, array(
                    'label' => $tipo_impegno == 'I' ? "Tipologia impegno" : "Tipologia disimpegno",
            'choices_as_values' => true,
            'choices' => \array_flip($this->getChoicesTipologiaImpegno($tipo_impegno, $options['enabledTr'])),
                    'required' => !$options['disabled'],
                    // 'disabled' => $options['disabled'],
                    'disabled' => $options['ruolo_lettura'],
                ))
                ->add('data_impegno', self::birthday, array(
                    'label' => $tipo_impegno == 'I' ? "Data impegno" : "Data disimpegno",
                    // 'disabled' => $options['disabled'],
                    'disabled' => $options['ruolo_lettura'],
                    'required' => !$options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('importo_impegno', self::moneta, array(
                    'label' => $tipo_impegno == 'I' ? "Importo impegno" : "Importo disimpegno",
                    // 'disabled' => $options['disabled'],
                    'disabled' => $options['ruolo_lettura'],
                    'required' => true,
        ));
        if ($tipo_impegno == 'D') {
            $builder->add('tc38_causale_disimpegno', self::entity, array(
                'class' => 'MonitoraggioBundle\Entity\TC38CausaleDisimpegno',
                'required' => true,
                'disabled' => $options['ruolo_lettura'],
                'label' => 'Causale disimpegno'
            ));
        }
        $builder->add('note_impegno', self::textarea, array(
                    'required' => false,
                    'disabled' => $options['ruolo_lettura'],
                    'label' => 'Note',
                ))
                ->add('submit', self::salva_indietro, array(
                    'url' => $options['url_indietro'],
                    'disabled' => $options['ruolo_lettura'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setRequired(array('enabledTr', 'url_indietro', 'ruolo_lettura'));
    }

    protected function getChoicesTipologiaImpegno($tipo_impegno, $enabledTr) {
        if ($tipo_impegno == 'I') {
            if ($enabledTr) {
                return array(
                    'I' => 'I',
                    'I-TR' => 'I-TR'
                );
            } else {
                return array('I' => 'I');
            }
        } else {
            if ($enabledTr) {
                return array(
                    'D' => 'D',
                    'D-TR' => 'D-TR'
                );
            } else {
                return array('D' => 'D');
            }
        }
    }

}
