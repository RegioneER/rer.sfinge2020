<?php

namespace AttuazioneControlloBundle\Form\IncrementoOccupazionale;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfermaIncrementoOccupazionaleType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('incremento_occupazionale_confermato', self::choice, [
            'choices' => ['Sì' => true, 'No' => false],
            'choices_as_values' => true,
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'mapped' => false,
            'data' => $options['data']->getAttuazioneControlloRichiesta()->isIncrementoOccupazionaleConfermato(),
            'placeholder' => '-',
            'label' => 'Confermi l\'incremento occupazionale indicato in fase di presentazione?',
        ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getForm()->getData();
           
            if ($data->getAttuazioneControlloRichiesta()->isIncrementoOccupazionaleConfermato()) {
                $event->getForm()->add('incremento_occupazionale', self::collection, [
                    'entry_type' => 'AttuazioneControlloBundle\Form\IncrementoOccupazionaleProponenteType',
                    'allow_add' => false,
                    'label' => false,
                    'entry_options' => []
                ]);
            }
        });
        
        $builder->add('pulsanti', self::salva_indietro, ['url' => $options['url_indietro']]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'AttuazioneControlloBundle\Entity\Pagamento']);
        $resolver->setRequired('url_indietro');
    }
}
