<?php
namespace SoggettoBundle\Form;

use AnagraficheBundle\Form\PersonaType;
use BaseBundle\Form\CommonType;
use SoggettoBundle\Form\Entity\NuovaPersonaFisica;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class NuovaPersonaFisicaType
 */
class NuovaPersonaFisicaType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('legaleRappresentante', PersonaType::class, [
            'url_indietro' => $options['url_indietro'],
            'disabled' => $options['disabled'],
            'label' => false,
            'disabilitaEmail' => false,
            'disabilitaCf' => true,
            'constraints' => [new Valid()],
        ]);

        $builder->add('email_pec', self::text, [
            'mapped' => false,
            'required' => false,
            'label' => 'Email PEC',
            'disabled' => $options['disabled'],
            //'constraints' => [new NotBlank()],
        ]);

        $builder->get('legaleRappresentante')->remove('telefono_secondario');
        $builder->get('legaleRappresentante')->remove('fax_secondario');
        $builder->get('legaleRappresentante')->remove('email_secondario');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NuovaPersonaFisica::class
        ]);
        $resolver->setRequired('url_indietro');
        $resolver->setRequired('tipo');
    }
}
