<?php

namespace AttuazioneControlloBundle\Form\IncrementoOccupazionale;

use BaseBundle\Form\CommonType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use AnagraficheBundle\Entity\TipologiaAssunzione;

class NuovoDipendenteType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nome', self::text, [
            'label' => 'Nome',
            'constraints' => [new NotNull()],
        ]);

        $builder->add('cognome', self::text, [
            'label' => 'Cognome',
            'constraints' => [new NotNull()],
        ]);

        $builder->add('data_assunzione', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'label' => 'Data assunzione a tempo indeterminato',
            'constraints' => [new NotNull()],
        ]);
        
        $builder->add('tipologia_assunzione', self::entity, [
            'class' => 'AnagraficheBundle\Entity\TipologiaAssunzione',
            'label' => 'Tipologia assunzione a tempo indeterminato',
            'constraints' => [new NotNull()],
            'placeholder' => '-',
            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('t')
                                        ->where('t.contesto IN (:contesto)')
                                        ->orderBy('t.descrizione', 'ASC')
                                        ->setParameter('contesto', [TipologiaAssunzione::CONTESTO_FULLTIME, TipologiaAssunzione::CONTESTO_PARTTIME], Connection::PARAM_STR_ARRAY);
            }, ]);

        $builder->add('pulsanti', self::salva_indietro, ['url' => $options['url_indietro'], ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'AnagraficheBundle\Entity\Personale',]);
        $resolver->setRequired('url_indietro');
    }
}
