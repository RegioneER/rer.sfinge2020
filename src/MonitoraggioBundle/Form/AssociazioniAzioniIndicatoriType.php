<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use MonitoraggioBundle\Repository\TC44_45IndicatoriOutputRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociazioniAzioniIndicatoriType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $disabled = !\is_null($builder->getData());

        $builder->add('azione', self::entity, [
            'label' => 'Azioni',
            'required' => true,
            'class' => \SfingeBundle\Entity\Azione::class,
            'disabled' => $disabled || $options['disabled'],
        ]);
        
        $builder->add('indicatoreOutput', self::entity, [
            'label' => 'Indicatore output',
            'required' => true,
            'class' => \MonitoraggioBundle\Entity\TC44_45IndicatoriOutput::class,
            'disabled' => $disabled || $options['disabled'],
            // 'query_builder' => function (TC44_45IndicatoriOutputRepository $er) {
            //     return $er->getIndicatoriVisibili();
            // }
        ]);

        $builder->add('asse', self::entity, [
            'label' => 'Asse',
            'class' => \SfingeBundle\Entity\Asse::class,
            'required' => true,
            'disabled' => $disabled || $options['disabled'],
        ]);
        
        $builder->add('validoDa', self::birthday, [
            'label' => 'Data inizio validità',
            'required' => false,
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy'
        ]);

        $builder->add('validoA', self::birthday, [
            'label' => 'Data fine validità',
            'required' => false,
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy'
        ]);
    

        $builder->add('submit', self::salva_indietro,[
            'url' => $options['url_indietro'],
        ]);        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => \MonitoraggioBundle\Entity\IndicatoriOutputAzioni::class,
        ));
        $resolver->setRequired('url_indietro');
    }
}