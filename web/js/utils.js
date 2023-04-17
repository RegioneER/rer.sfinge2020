Number.prototype.formatMoney = function(c, d, t){
	var n = this,
	s = n < 0 ? "-" : ""; 
	
	c = isNaN(c = Math.abs(c)) ? 2 : c;
	d = d === undefined ? "," : d;
	t = t === undefined ? "." : t;
	
	var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
		j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

String.prototype.deformatMoney = function() {
	return this.replace(/(\.|,)(?=.*[\.|,]\d+$)/g, '').replace(/,/g, '.');
}

Number.prototype.trueRound = function(digits){
    return (Math.round((this*Math.pow(10,digits)).toFixed(digits-1))/Math.pow(10,digits)).toFixed(digits);
}

Number.prototype.pattern = /^-?\d{1,3}(\.?\d{3})*(,\d{2})?$/; 

function getImportoFormattato(importo) {
	if (importo === '') {
		return '';
	}

	importo = importo.replace(/\./g, ',');
	importo = importo.replace(/\+/g, '');
	importo = importo.replace(/ /g, '');

	var token = importo.split(',');
	var parteIntera = token[0] === "-0" ? "-0" : parseInt(token[0]).toString();
	if (isNaN(parteIntera)) {
		parteIntera = '';
	}

	var parteDecimale = ',' + (isNaN(token[1]) ? '00' : token[1]);

	var negativo = false;
	if (importo.search(/-/g) >= 0) {
		negativo = true;
		parteIntera = parteIntera.replace(/-/g, '');
	}

	var parteInteraLength = parteIntera.length;

	var rest = parteInteraLength % 3;
	var block = parseInt(parteInteraLength / 3);

	var out = '';

	for (var i = 1; i <= block; i++) {
		out = parteIntera.substr(parteInteraLength - i * 3, 3) + out;
		if (i < block) {
			out = '.' + out;
		}
	}

	if (rest > 0) {
		out = block > 0 ? parteIntera.substr(0, rest) + '.' + out : parteIntera;
	}

	if (negativo === true) {
		out = '-' + out;
	}

	return out + parteDecimale;
}

jQuery.fn.extend({
  approximate: function() {
    return this.each(function() {
		if (jQuery(this).val() !== '') {
			var value = jQuery(this).val().deformatMoney();
			var numeric_value = parseFloat(value).trueRound(2);
			var new_value = getImportoFormattato(numeric_value);
			jQuery(this).val(new_value);
		} 
    });
  },
  formatAmount: function() {
	var value = jQuery(this).val();
	var cursor = jQuery(this).caret();
	// var unformatted_value = parseFloat(value.deformatMoney());
	// var newValore = unformatted_value.formatMoney();//getImportoFormattato(unformatted_value);
	var unformatted_value = value.deformatMoney();
	var newValore = getImportoFormattato(unformatted_value);
	if (newValore.length - value.length === 1) {
		cursor++;
	} else if (newValore.length - value.length === -1) {
		cursor--;
	}
	if(value.search(',,') === cursor){
		cursor++;
	}

	jQuery(this).val(newValore);
	jQuery(this).caret(cursor);	  
  }
});

function roundHalfUp(value, decimals) {
  return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
}
