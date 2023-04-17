<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 03/07/17
 * Time: 11:55
 */

namespace MonitoraggioBundle\Form\Ricerca;

use Symfony\Component\Form\FormBuilderInterface;

class AP01Type extends BaseType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        parent::buildForm($builder, $options);

        $builder->add('cod_locale_progetto', self::text, array(
            'required' => false,
            'label' => 'Codice Locale Progetto',
        ));

        $builder->add('tc1_procedura_attivazione', self::entity, array(
            'required' => false,
            'label' => 'Procedura Attivazione',
            'placeholder' => '-',
            'choices' => $options['procedure'],
            'class' => 'MonitoraggioBundle:TC1ProceduraAttivazione',
        ));


        $builder->add('flg_cancellazione', self::choice, array(
            'required' => false,
            'label' => 'Flag cancellazione',
            'choices_as_values' => true,
            'choices' => array(
                'Sì' => 'S',
            ),
            'placeholder' => 'No',
        ));


    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired( array(
            'procedure',
        ));
    }

}
