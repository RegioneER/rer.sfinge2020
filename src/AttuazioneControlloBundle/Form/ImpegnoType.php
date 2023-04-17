<?php
/**
 * @author lfontana
 */

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use MonitoraggioBundle\Entity\TC38CausaleDisimpegno;
use SfingeBundle\Entity\IngegneriaFinanziaria;

class ImpegnoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var RichiestaImpegni $data */
        $data = $builder->getData();
        $richiesta = $data->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $builder
        ->add('tipologia_impegno', self::choice, [
            'choices_as_values' => true,
            'choices' => 
                \array_flip(
                    $procedura instanceof IngegneriaFinanziaria ? 
                        RichiestaImpegni::$TIPOLOGIE_IMPEGNI_TRASFERIMENTO : 
                        RichiestaImpegni::$TIPOLOGIE_IMPEGNI_AMMESSI
                ),
            'label' => 'Tipologia di impegno/disimpegno',
            'required' => !$options['disabled'],
            'disabled' => false,
        ])
        ->add('data_impegno', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'label' => "Data dell'impegno/disimpegno",
            'required' => !$options['disabled'],
        ])
        ->add('importo_impegno', self::moneta, [
            'label' => 'Importo',
            'required' => !$options['disabled'],
        ])
        ->add('tc38_causale_disimpegno', self::entity, [
            'class' => TC38CausaleDisimpegno::class,
            'required' => false,
            'label' => 'Causale del disimpegno',
            'placeholder' => '-',
        ])
        ->add('submit', self::salva_indietro, [
            'url' => $options['url_indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setRequired('url_indietro')
        ->setDefaults([
            'data_class' => RichiestaImpegni::class,
        ]);
    }
}
