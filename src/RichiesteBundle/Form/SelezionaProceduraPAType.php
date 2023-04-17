<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotNull;
use SfingeBundle\Entity\StatoProcedura;


class SelezionaProceduraPAType extends CommonType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData();
        $builder->add('procedura', self::entity, array(
            'class' => 'SfingeBundle\Entity\ProceduraPA',
            'label' => 'Bando',
            'required' => true,
            'choices' => $options['procedure']
        ))
        ->add('soggetto', self::entity, array(
            'class' => 'SoggettoBundle\Entity\Soggetto',
            'query_builder' => function(EntityRepository $er) use($data){
                return $er->createQueryBuilder('soggetto')
                ->where('soggetto = :soggetto',
						'soggetto NOT INSTANCE OF :ooii')
                ->setParameter('soggetto', \is_null($data) ? NULL : $data->getSoggetto())
				->setParameter('ooii', 'OOII');
            },
            'label' => 'Soggetto della richiesta',
            'required' => true,
            'constraints' => array(
                new NotNull(),
            ),
        ))
        ->add('submit', self::salva_indietro,array(
            'mostra_indietro' => false,
            'url' => false,
        ))
        ->addEventListener(FormEvents::PRE_SUBMIT,function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();

            $form->add('soggetto', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                'class' => 'SoggettoBundle\Entity\Soggetto',
                'query_builder' => function(EntityRepository $er) use($data){
                    return $er->createQueryBuilder('soggetto')
                    ->where('soggetto.id = :soggetto')
                    ->setParameter('soggetto', \is_null($data) || !\array_key_exists('soggetto', $data ) ? NULL : $data['soggetto']);
                },
                'label' => 'Soggetto della richiesta',
                'required' => true,
                'constraints' => array(
                    new NotNull(),
                ),
            ));
        });
    }
	
	public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
			'readonly' => false,
        ));
		$resolver->setRequired("readonly");
        $resolver->setRequired("disabled");
		$resolver->setRequired("procedure");
    }
}