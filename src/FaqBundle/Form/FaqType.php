<?php

namespace FaqBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FaqType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titolo')
            ->add('testo')
            ->add('dataInserimento', 'date',array('widget' => 'single_text','input' => 'datetime','format' => 'dd/MM/yyyy'))
			->add('dataInserimento', self::birthday, array(
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy',
				'required' => true,
				'label' => 'Data Inserimento'))
		    ->add('pulsanti',self::salva_indietro,array("url"=>$options["url_indietro"]));
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FaqBundle\Entity\Faq'
        ));
        $resolver->setRequired("url_indietro");
		$resolver->setRequired("visibilita");
    }
}
