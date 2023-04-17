<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class IntegrazioneType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('testo', self::textarea, [
                'label' => 'Testo',
                'required' => false,
                'attr' => ['rows' => 6]
            ]
        );
        
        $builder->add('testoEmail', self::textarea, [
                'label' => 'Testo email',
                'required' => true,
                'constraints' => array(new NotNull()),
                'attr' => array('rows' => 6)
            ]
        );
        
        if ($options['mostra_giorni_per_risposta']) {
            $builder->add('giorniPerRisposta', self::integer, [
                'required' => false,
                    ]
            );
        }
        
        $builder->add('pulsanti', 'BaseBundle\Form\SalvaInvioIndietroType', 
            ["url" => $options["url_indietro"],  "label" => false, "disabled" => false]);
        
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\IntegrazionePagamento'
        ]);
        
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("mostra_giorni_per_risposta");
    }
}
