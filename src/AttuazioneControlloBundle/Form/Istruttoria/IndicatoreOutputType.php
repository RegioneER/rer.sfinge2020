<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\IndicatoreOutput;



class IndicatoreOutputType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var IndicatoreOutput $indicatore */
        $indicatore = $builder->getData();

        $builder->add('val_programmato',self::numero,[
            'disabled' => true,
            'label' => 'Valore programmato',
        ])
        ->add('valore_realizzato',self::numero,[
            'disabled' => true,
            'label' => 'Valore realizzato dichiarato da beneficiario'
        ])
        ->add('valore_validato', self::numero,[
            'label' => 'Valore realizzato validato da istruttore',
            'data' => \is_null($indicatore->getValoreValidato()) ? $indicatore->getValoreRealizzato() :$indicatore->getValoreValidato(),
        ])
        ->add('submit', self::salva_indietro,[
            'url' => $options['url_indietro']
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => IndicatoreOutput::class,
        ])
        ->setRequired('url_indietro');
    }
}