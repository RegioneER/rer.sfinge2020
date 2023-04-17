<?php

namespace AttuazioneControlloBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use BaseBundle\Form\CommonType;

class ProgettoProceduraAggiudicazioneType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var AttuazioneControlloRichiesta $atc */
        $atc = $builder->getData();

        $builder->add('procedure_aggiudicazione', self::choice,[
            'label' => 'Le attività di progetto sono state realizzate attraverso procedure regolate dal vigente Codice dei Contratti e dalla normativa che lo applica',
            'choices_as_values' => true,
            'choices' => [
                'Sì' => true,
                'No' => false,
            ],
            'required' => true,
        ]);
        $salvaOptions = ['label' => false,];
        // $salvaOptions = ['url' => $options['url_indietro'],];
        if($atc->getProcedureAggiudicazione() === false && $atc->getRichiesta()->getMonProcedureAggiudicazione()->count() > 0 ){
            $salvaOptions['attr'] = [
                'data-confirm' => "Attenzione verranno cancellati i dati precedentemente immessi, ma non validati",
            ];
        }
        $builder->add('submit', self::salva, $salvaOptions);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => AttuazioneControlloRichiesta::class,
        ])
        // ->setRequired('url_indietro')
        ;
    }
}