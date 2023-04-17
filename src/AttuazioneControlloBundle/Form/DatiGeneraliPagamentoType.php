<?php
namespace AttuazioneControlloBundle\Form;

use AttuazioneControlloBundle\Entity\Pagamento;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiGeneraliPagamentoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Pagamento $pagamento */
        $pagamento = $options['data'];
        $isRichiestaFirmaDigitale = $pagamento->getProcedura()->isRichiestaFirmaDigitale();
        if ($isRichiestaFirmaDigitale) {
            $etichettaFirmatario = "Firmatario";
        } else {
            $etichettaFirmatario = "Persona che invierà il pagamento";
        }

        $builder->add('firmatario', self::entity, [
            'class' => "AnagraficheBundle\Entity\Persona",
            "label" => $etichettaFirmatario,
            'choice_label' => function ($persona) {
                return $persona->getNome() . " " . $persona->getCognome() . " ( " . $persona->getCodiceFiscale() . " )";
            },
            "choices" => $options["firmatabili"],
            'placeholder' => '-',
            'constraints' => [new NotNull(["groups" => "dati_generali"])],
        ]);

        $builder->add("pulsanti", self::salva_indietro, ["url" => $options["url_indietro"]]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento',
            //se in fase di ATC la richiesta è stata categorizzata per soggetto pubblico allora si rilassa il vincolo sul formato IBAN
            'validation_groups' => function (\Symfony\Component\Form\FormInterface $form) {
                $pagamento = $form->getData();
                $groups = ['dati_generali'];
                // volutamente scritto in negato in modo da essere vincolante in caso di tipo soggetto non valorizzato
                //dovremmo predisporre una bonifica pe ril pregresso
                if (!$pagamento->getRichiesta()->getIstruttoria()->isSoggettoPubblico()) {
                    $groups[] = 'iban_obbligatorio';
                }

                return $groups;
            },
        ]);
        $resolver->setRequired("firmatabili");
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipologia");
    }
}
