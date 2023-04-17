<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FascicoloType extends CommonType {
    public function __construct() {
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('titolo', self::text, array('required' => true, 'label' => 'Titolo'));
        $builder->add('alias', self::text, array('required' => true, 'label' => 'Alias'));
        $builder->add('template', self::text, array('required' => true, 'label' => 'Template'));
        $builder->add('callback', self::text, array('required' => false, 'label' => 'Callback validazione'));
        if ($options['button']) {
            $builder->add('submit', SubmitType::class, array('label' => 'Salva'));
        }
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'FascicoloBundle\Entity\Fascicolo',
            'constraints' => array(new Valid()),
            'button' => false,
        ));
    }
}
