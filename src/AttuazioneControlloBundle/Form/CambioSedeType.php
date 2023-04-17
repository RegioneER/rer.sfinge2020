<?php

namespace AttuazioneControlloBundle\Form;

use AttuazioneControlloBundle\Entity\VariazioneSedeOperativa;
use BaseBundle\Form\CommonType;
use SoggettoBundle\Entity\Sede;
use SoggettoBundle\Entity\SedeRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CambioSedeType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var VariazioneSedeOperativa $variazione */
        $variazione = $builder->getData();

        $richiesta = $variazione->getRichiesta();
        $sediOperative = $richiesta->getMandatario()->getSedi();

        $builder->add('sede_operativa', self::entity, [
            'label' => 'Attuale UL/sede del progetto',
            'class' => Sede::class,
            'choice_label' => function (Sede $sede) {
                return $sede->getDenominazione() . ' - ' . $sede->getIndirizzo();
            },
            'query_builder' => function (SedeRepository $repo) use ($variazione) {
                $qb = $repo->createQueryBuilder('sede');
                return $qb
                ->join('sede.sedeOperativa', 'so')
                ->join('so.proponente', 'proponente', 'WITH', 'proponente.mandatario = 1')
                ->join('proponente.richiesta', 'richiesta')
                ->join('richiesta.attuazione_controllo', 'atc')
                ->join('atc.variazioni', 'variazioni')
                ->where('variazioni = :variazione')
                ->setParameter('variazione', $variazione);
            },
            'placeholder' => $sediOperative->isEmpty() ? $richiesta->getSoggetto() : false,
        ]);
        $builder->add('sede_operativa_variata', self::entity, [
            'label' => 'Nuova UL/sede del progetto',
            'class' => Sede::class,
            'choice_label' => function (Sede $sede) {
                return $sede->getDenominazione() . ' - ' . $sede->getIndirizzo();
            },
            'query_builder' => function (SedeRepository $repo) use ($variazione) {
                $richiesta = $variazione->getRichiesta();

                return $repo->getNuoveSediNonAssociate($richiesta);
            },
        ]);
        $builder->add('autodichiarazione', self::checkbox, [
            'label' => "La nuova UL / sede progetto rispecchia i criteri previsti dal bando in merito alla localizzazione territoriale della sede di intervento",
            'required' => true,
        ]);

        $builder->add('submit', self::salva_indietro, [
            'url' => $options['indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => VariazioneSedeOperativa::class,
        ]);

        $resolver->setRequired('indietro');
    }
}
