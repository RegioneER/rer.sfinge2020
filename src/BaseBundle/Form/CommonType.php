<?php

namespace BaseBundle\Form;

use Symfony\Component\Form\AbstractType;

class CommonType extends AbstractType {
		
	const choice = 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
	const text = 'Symfony\Component\Form\Extension\Core\Type\TextType';
	const entity = 'Symfony\Bridge\Doctrine\Form\Type\EntityType';
	const birthday = 'Symfony\Component\Form\Extension\Core\Type\BirthdayType';
	const datetime = 'Symfony\Component\Form\Extension\Core\Type\DateTimeType';
	const hidden = 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
	const submit = 'Symfony\Component\Form\Extension\Core\Type\SubmitType';
	const checkbox = "Symfony\Component\Form\Extension\Core\Type\CheckboxType";
	const collection = "Symfony\Component\Form\Extension\Core\Type\CollectionType";
	const textarea = 'Symfony\Component\Form\Extension\Core\Type\TextareaType';

	const documento = 'DocumentoBundle\Form\Type\DocumentoFileType';
	const documento_simple = 'DocumentoBundle\Form\Type\DocumentoFileSimpleType';
	const email = 'Symfony\Component\Form\Extension\Core\Type\EmailType';
	const integer = 'Symfony\Component\Form\Extension\Core\Type\IntegerType';

	const file = 'Symfony\Component\Form\Extension\Core\Type\FileType';

	const entity_hidden = "BaseBundle\Form\EntityHiddenType";

	const bottone = "Symfony\Component\Form\Extension\Core\Type\ButtonType";

	const indirizzo = "BaseBundle\Form\IndirizzoType";

	const salva_indietro = "BaseBundle\Form\SalvaIndietroType";
	const salva_invia_indietro = "BaseBundle\Form\SalvaInvioIndietroType";
	const salva_blocca_indietro = "BaseBundle\Form\SalvaBloccaIndietroType";
	const valida_invalida_indietro = "BaseBundle\Form\ValidaInvalidaIndietroType";

	const link = "BaseBundle\Form\LinkType";
	const indietro = "BaseBundle\Form\IndietroType";
	const salva = "BaseBundle\Form\SalvaType";

	const generico = "Symfony\Component\Form\Extension\Core\Type\FormType";
	const numero = "Symfony\Component\Form\Extension\Core\Type\NumberType";
	
	const moneta = "Symfony\Component\Form\Extension\Core\Type\MoneyType";
	const importo = "BaseBundle\Form\ImportoType";
	
	const AdvancedTextType = "FascicoloBundle\Form\Type\AdvancedTextType";
    
	public function mapping($currentChoiceKey) {
		if (is_null($currentChoiceKey)) { return ''; }
		return $currentChoiceKey ? '1' : '0';
	}        
}
