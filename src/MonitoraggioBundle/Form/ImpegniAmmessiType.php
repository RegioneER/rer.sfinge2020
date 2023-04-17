<?php

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;

class ImpegniAmmessiType extends CommonType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData();
        $tipo_impegno = $data->getRichiestaImpegni()->getTipologiaImpegno();
        $tipo_impegno = $tipo_impegno[0];
        
        $builder->add('richiesta_livello_gerarchico', self::entity, array(
                    'label' => 'Livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'class' => 'AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use($data){
                        return $er->createQueryBuilder('u')
                            ->join('u.richiesta_programma', 'richiesta_programma' )
                            ->join('richiesta_programma.richiesta', 'richiesta')
                            ->where('richiesta = :richiesta')
                            ->setParameter('richiesta',$data->getRichiestaImpegni()->getRichiesta());
                    }
                ))
                ->add('data_imp_amm', self::birthday, array(
                    'label' => $tipo_impegno == 'I' ? "Data impegno ammesso" : "Data disimpegno ammesso",
                    'disabled' => true,
                    'required' => !$options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('tipologia_imp_amm', self::choice, array(
                    'label' => $tipo_impegno == 'I' ? "Tipologia impegno ammesso" : "Tipologia disimpegno ammesso",
                    'disabled' => true,
                    'required' => !$options['disabled'],
                    "placeholder" => '-',
					'choices_as_values' => true,
                    "choices" => \array_flip(\AttuazioneControlloBundle\Entity\ImpegniAmmessi::$TIPOLOGIE_IMPEGNI_AMMESSI),
                ))
                ->add('importo_imp_amm', self::moneta, array(
                    'label' => $tipo_impegno == 'I' ? "Importo impegno ammesso" : "Importo disimpegno ammesso",
                    'disabled' => $options['disabled'],
                    'required' => true,
                ));
                if ($tipo_impegno == 'D') {
                    $builder->add('tc38_causale_disimpegno_amm', self::entity, array(
                        'class' => 'MonitoraggioBundle\Entity\TC38CausaleDisimpegno',
                        'disabled' => true,
                        'required' => true,
                        'label' => 'Causale disimpegno ammesso'
                    ));
                }
                $builder->add('note_imp', self::textarea, array(
                    'label' => 'Note',
                    // 'disabled' => $options['disabled'],
                    'disabled' => $options['ruolo_lettura'],
                    'required' => false,
                ))
                ->add('submit', self::salva_indietro, array(
                    "url" => $options["url_indietro"],
                    // 'disabled' => false,
                    'disabled' => $options['ruolo_lettura'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\ImpegniAmmessi',
        ));
        $resolver->setRequired(array('url_indietro'));
        $resolver->setRequired(array('ruolo_lettura'));

    }

}
