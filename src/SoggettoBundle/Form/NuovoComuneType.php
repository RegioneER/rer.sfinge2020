<?php

namespace SoggettoBundle\Form;

use AnagraficheBundle\Form\PersonaType;
use BaseBundle\Form\CommonType;
use SoggettoBundle\Form\Entity\NuovoComune;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class NuovoComuneType
 *
 * @package SoggettoBundle\Form
 */
class NuovoComuneType extends CommonType {

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('comune', ComuneUnioneType::class, [
            'url_indietro' => '',
            'tipo' => $options['tipo'],
            'readonly' => $options['disabled'],
            'label' => false,
            'constraints' => array(new Valid()),
        ]);
        $builder->get('comune')->remove('pulsanti');

        $builder->add('legaleRappresentante', PersonaType::class, [
            'url_indietro' => $options['url_indietro'],
            'readonly' => $options['disabled'],
            'label' => false,
            'disabilitaEmail' => false,
            'constraints' => array(new Valid()),
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => NuovoComune::class
        ]);
        $resolver->setRequired('url_indietro');
        $resolver->setRequired('tipo');
    }
}
