<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AP02InformazioniGeneraliType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('generatore_entrate', self::choice, array(
                    'label' => 'Generatore di entrate',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array( 'Sì' => 'S', 'No' => 'N' ),
                    'placeholder' => '-',
                ))
                ->add('fondo_di_fondi', self::choice, array(
                    'label' => 'Fondo di fondi',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array( 'Sì' => 'S', 'No' => 'N' ),
                    'placeholder' => '-',
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices' => array('Sì' => 'S'),
                    'choices_as_values' => true,
                    'placeholder' => 'No',
                ))
                ->add('tc7_progetto_complesso', self::entity, array(
                    'label' => 'Tipo progetto complesso',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC7ProgettoComplesso",
                ))
                ->add('tc8_grande_progetto', self::entity, array(
                    'label' => 'Grande progetto',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC8GrandeProgetto",
                ))
                ->add('tc9_tipo_livello_istituzione', self::entity, array(
                    'label' => '"Tipo livello istituzione finanziaria',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC9TipoLivelloIstituzione",
                ))
                ->add('tc10_tipo_localizzazione', self::entity, array(
                    'label' => 'Tipo localizzazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC10TipoLocalizzazione",
                ))
                ->add('tc13_gruppo_vulnerabile_progetto', self::entity, array(
                    'label' => 'Gruppo vulnerabile progetto',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto",
                ))
                ->add('submit', self::salva_indietro, array(
                    "url" => $options["url_indietro"],
                    'disabled' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
    }

}
