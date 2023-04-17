<?php
namespace ProtocollazioneBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaLogType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		parent::buildForm($builder, $options);
        $builder->add('richiestaProtocolloId', TextType::class,
            ['required' => false, 'label' => 'Id richiesta protocollo']);
        $builder->add('appFunctionTarget', ChoiceType::class,
            ['required' => false, 'label' => 'App function target', 'choices' => ['RESP' => 'RESP', 'REQU' => 'REQU',],]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
	public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setDefaults([
            'data_class' => 'ProtocollazioneBundle\Form\Entity\RicercaLog',
        ]);
	}
}
