<?php
namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;
use AttuazioneControlloBundle\Entity\PagamentoAmmesso;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use MonitoraggioBundle\Entity\TC39CausalePagamento;
use AttuazioneControlloBundle\Repository\RichiestaLivelloGerarchicoRepository;

class PagamentoAmmessoType extends CommonType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        /** @var PagamentoAmmesso $data */
        $data = $builder->getData();
        $pagamento = is_null($data) ? NULL : $data->getRichiestaPagamento();
        $builder
            ->add('livello_gerarchico', self::entity, array(
            'class' => RichiestaLivelloGerarchico::class,
            'label' => 'Livello gerarchico',            
            'required' => !$options['disabled'],
            'query_builder' => function(RichiestaLivelloGerarchicoRepository $er) use($data){
                return $er
                    ->createQueryBuilder('liv')
                    ->join('liv.richiesta_programma', 'programma')
                    ->join('programma.richiesta', 'richiesta')
                    ->where('richiesta = :richiesta')
                    ->setParameter('richiesta', $data->getRichiestaPagamento()->getRichiesta());
            },
        ))
            ->add('data_pagamento', self::birthday, array(
            'label' => 'Data del pagamento ammesso',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => !$options['disabled'],
        ))
            ->add('tipologia_pagamento', self::choice, [
                'label' => 'Tipologia pagamento ammesso',
                'required' => true,
                    'choices_as_values' => true,
                    'choices' => [
                        'Pagamento' => 'P',
                        'Rettifica' => 'R',
                        'Pagamento per Trasferimento' => 'P-TR',
                        'Rettifica su Trasferimento' => 'R-TR',
                ]
            ])
            ->add('causale', self::entity, array(
            'class' => TC39CausalePagamento::class,
            'label' => 'Causale',
            'required' => !$options['disabled'],
        ))
            ->add('importo', self::moneta, array(
            'label' => 'Importo',
            'required' => !$options['disabled'],
        ))
            ->add('note', self::textarea, array(
            'label' => 'Note',
            'required' => false,
        ))




            ->add('submit', self::salva_indietro, array(
            'url' => $options['url_indietro'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setRequired('url_indietro')
            ->setDefaults(array(
            'data_class' => PagamentoAmmesso::class,
        ));
    }
} 
