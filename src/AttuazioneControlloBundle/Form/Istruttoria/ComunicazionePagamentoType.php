<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\TipologiaComunicazionePagamento;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ComunicazionePagamentoType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tipologia_comunicazione', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
            'class' => 'AttuazioneControlloBundle\Entity\Istruttoria\TipologiaComunicazionePagamento',
            'choice_label' => function (TipologiaComunicazionePagamento $tipologie) {
                return $tipologie->getDescrizione();
            },
        ));
        
        $builder->add('testo', self::textarea, array(
                'label' => 'Testo',
                'required' => true,
                'attr' => array('rows' => 6)
            )
        );
        
        $builder->add('testoEmail', self::textarea, array(
                'label' => 'Testo email',
                'required' => true,
                'constraints' => array(new NotNull()),
                'attr' => array('rows' => 6)
            )
        );
        
        $builder->add('pulsanti', 'BaseBundle\Form\SalvaInvioIndietroType', array("url" => $options["url_indietro"],  "label" => false, "disabled" => false));
        
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\ComunicazionePagamento'
        ));
        
        $resolver->setRequired("url_indietro");
    }
}
