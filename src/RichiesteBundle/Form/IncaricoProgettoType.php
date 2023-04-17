<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Doctrine\ORM\QueryBuilder;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\IncaricoPersonaRepository;
use SoggettoBundle\Entity\IncaricoPersonaRichiesta;
use SoggettoBundle\Entity\StatoIncarico;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncaricoProgettoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $form = $event->getForm();
            
            /** @var IncaricoPersonaRichiesta */
            $incaricoRichiesta = $event->getData();
            $richiesta = $incaricoRichiesta->getRichiesta();
            
            $form->add('incarico_persona', self::entity, [
                'class' => IncaricoPersona::class,
                'query_builder' => function (IncaricoPersonaRepository $repo) use($richiesta): QueryBuilder {
                    return $repo->createQueryBuilder('i')
                    ->join('i.tipo_incarico', 'tipo_incarico', 'WITH', 'tipo_incarico.codice = :operatore')
                    ->join('i.stato', 'si', 'WITH', 'si.codice = :attivo')
                    ->join('i.soggetto', 's')
                    ->join('s.proponenti', 'p', 'WITH', 'p.mandatario = 1')
                    ->join('p.richiesta', 'r')
                    ->where('r = :richiesta')
                    ->setParameter('operatore', TipoIncarico::OPERATORE_RICHIESTA)
                    ->setParameter('attivo', StatoIncarico::ATTIVO)
                    ->setParameter('richiesta', $richiesta);
                },
                'choice_label' => 'incaricato',
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => IncaricoPersonaRichiesta::class,
            'empty_data' => function(FormInterface $form){
                $richiesta = $form->getParent()->getData();
                return new IncaricoPersonaRichiesta($richiesta);
            },
        ]);
    }
}
