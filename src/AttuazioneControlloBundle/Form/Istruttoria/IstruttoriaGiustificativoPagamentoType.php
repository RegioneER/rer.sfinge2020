<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IstruttoriaGiustificativoPagamentoType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('voci_piano_costo', self::collection, [
            'entry_type' => 'AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaVocePianoCostoGiustificativoType',
            'allow_add' => false,
            'label' => false,
            'entry_options' => ['ripresentazione_spesa' => $options['ripresentazione_spesa'],],
        ]);

        $builder->add('pulsanti', self::salva_indietro, [
            'label' => false,
            'disabled' => false,
            'label_indietro' => 'Modifica voci imputazione',
            'url' => $options['url_modifica_imputazione'],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\GiustificativoPagamento',
            'validation_groups' => ['Istruttoria'],
            // è stato aggiunto questo validation group per bypassare quello di default su quale è mappato un assert di tipo expression sull'entità giustificativopagamento
            //che ha senso solo lato beneficiario
        ]);

        $resolver->setRequired('url_modifica_imputazione');
        $resolver->setRequired('ripresentazione_spesa');
    }
}
