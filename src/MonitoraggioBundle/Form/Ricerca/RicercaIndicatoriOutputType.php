<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 02/01/18
 * Time: 11:48
 */

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaIndicatoriOutputType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        parent::buildForm($builder, $options);

        $builder->add('procedura', self::entity, array(
            'class' => 'SfingeBundle\Entity\Procedura',
            'placeholder' => '-',
            'required' => false,
            'label' => 'Procedura attivazione'
        ));

        $builder->add('azione', self::entity, array(
            'class' => 'SfingeBundle\Entity\Azione',
            'placeholder' => '-',
            'label' => 'Azione',
            'required' => false,
        ));

    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaIndicatoriOutput'
        ));
    }

}






















