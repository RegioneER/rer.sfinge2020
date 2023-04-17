<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use RichiesteBundle\Entity\Bando127\OggettoSanificazione;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class SedeOperativaRichiestaType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('denominazione', self::text, [
            'label' => 'Denominazione',
            'required' => true, 
            'disabled' => $options['disabled'],
            'constraints' => [
                new NotNull(),
                new Length(['min' => 2, 'max' => 1000])
            ],
        ]);

        $builder->add('indirizzo', self::indirizzo, [
            'label' => false,
            'disabled' => $options['disabled'],
            'validation_groups' => ['sede'],
        ]);

        $builder->add('tipologia', self::choice, [
            'label' => "Tipologia di unità locale",
            'choices' => OggettoSanificazione::TIPOLOGIE_UNITA_LOCALE,
            'choices_as_values' => false,
            'placeholder' => '-',
            'constraints' => [new NotNull()],
        ]);

        $builder->add('importoFinanziamento', self::importo, [
                'label' => 'Importo del finanziamento agevolato',
                'required' => true,
                'scale' => 2,
                'disabled' => $options['disabled'],
                'currency' => 'EUR',
                'grouping' => true,
                'constraints' => [new NotNull()],
            ]
        );
        
        $builder->add('importoSede', self::importo, [
                'label' => 'Importo spese da sostenere',
                'required' => true,
                'scale' => 2,
                'disabled' => $options['disabled'],
                'currency' => 'EUR',
                'grouping' => true,
                'constraints' => [new NotNull(),
                    new Range([
                            'min' => 2000,
                            'minMessage' => 'L\'importo spese da sostenere non può essere inferiore a 2.000,00€.',
                            ]
                    )]
            ]
        );

        $builder->add('contributoSede', self::importo, [
                'label' => 'Contributo richiesto',
                'required' => true,
                'scale' => 2,
                'disabled' => $options['disabled'],
                'currency' => 'EUR',
                'grouping' => true,
                'constraints' => [new NotNull(), 
                    new Range([
                    'min' => 0.01,
                    'max' => OggettoSanificazione::CONTRIBUTO_MASSIMO,
                    'minMessage' => 'Inserire un importo superiore a 0€.',
                    'maxMessage' => 'Il contributo richiesto non può superare la somma di 5.000€.']
                )]
            ]
        );

        $builder->add('autodichiarazione', self::checkbox, [
            'label' => 'Il Confidi proponente dichiara, in riferimento alla Delibera Num. 391 del 24/04/2020 
                “Contributi a fondo perduto finalizzati alla messa in sicurezza sanitaria da COVID-19”, 
                di aver ricevuto da parte dell’impresa di cui alla presente domanda, la richiesta di contributo avente 
                per oggetto interventi ammissibili ai sensi della DGR sopra citata. Il Confidi proponente si impegna a 
                conservare, presso i propri uffici, la documentazione attestante tale richiesta”.',
            'required' => true,
            'disabled' => $options['disabled'],
            'constraints' => [new NotBlank()],
        ]);

        $builder->add('pulsanti', self::salva_indietro, [
            'url' => $options['url_indietro'], 
            'disabled' => $options['disabled']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RichiesteBundle\Entity\SedeOperativaRichiesta',
            'dataIndirizzo' => null,
            'validation_groups' => ['Default', 'sede'],
        ]);

        $resolver->setRequired('url_indietro');
    }
}
