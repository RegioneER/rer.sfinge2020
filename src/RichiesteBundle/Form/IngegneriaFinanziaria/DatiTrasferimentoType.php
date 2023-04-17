<?php

namespace RichiesteBundle\Form\IngegneriaFinanziaria;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiTrasferimentoType extends \RichiesteBundle\Form\RichiestaType {


    public function getName() {
		return "dati_trasferimento";
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('atto', self::entity,  array('class' => 'SfingeBundle\Entity\Atto',
			'choice_label' => function ($atto) {
		        return $atto->getTitolo();
		    },
			'placeholder' => '-',
			'required' => true,
			'label' => 'Atto di trasferimento del fondo',
			'constraints' => array(new NotNull()),
			'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($options){
					return $er->createQueryBuilder('a')
							->where("a.procedura = {$options['procedura_id']}")
							->andWhere('a.data_cancellazione IS NULL'); 
			}));	
       
        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
    }
    
    /*
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
           
            'readonly' => false			
        ));
        $resolver->setRequired("url_indietro");
		$resolver->setRequired("procedura_id");
    }
}

