<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Form\Entity\ModificaVociImputazioneGiustificativo;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ModificaVociImputazioneGiustificativoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var ModificaVociImputazioneGiustificativo $modificaVoci */
        $modificaVoci = $builder->getData();
        $giustificativo = $modificaVoci->getGiustificativo();
        $tot = $giustificativo->getImportoRichiesto();
        $prototype = new VocePianoCostoGiustificativo();
        $prototype->setGiustificativoPagamento($giustificativo);

        $builder->add('voci', self::collection, [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => ModificaSingolaVoceImputazioneGiustificativoType::class,
            'prototype_data' => $prototype,
            'by_reference' => false,
            'label' => false,
            'constraints' => [
                new Callback(['callback' => function(Collection $voci, ExecutionContextInterface $context) use($tot){
                    $calcolato = \array_reduce($voci->toArray(), function(float $carry, VocePianoCostoGiustificativo $v ){
                        return $carry + $v->getImporto();
                    }, 0.0);
                    if(\bccomp($tot, $calcolato, 2) == 0 ){
                        return;
                    }
                    $context->addViolation('La somma degli importi non è uguale all\'importo richiesto');
                }])
            ],
        ]);
        $builder->add("submit", self::salva_indietro, [
            'url' => $options['url_indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ModificaVociImputazioneGiustificativo::class,
        ]);
        $resolver->setRequired("url_indietro");
    }
}
