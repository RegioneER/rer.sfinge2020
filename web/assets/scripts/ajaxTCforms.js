'use strict';

function settaValoriComuni(ricerca) {
var tabella = ricerca.attr('data-ajax-class');

var query = {
    'keys' : ricerca.val()
};
var parametri = query == null ? '' : jQuery.param(query);
var result = $('select[data-ajax-value]');
result.attr('disabled',true);
var url = (window.location.pathname.indexOf("strutture/modifica") > 0 ? '../':'')+ '../api/tc/' + tabella +  '?' + parametri;
$.getJSON(url)
        .done(function (data) {
            result.attr('disabled',false);
            result
                    .find('option')
                    .remove()
                    .end()
                    .trigger('change');

            $.each(data, function (k, v) {
                result.append($('<option>', {
                    value: v.id
                })
                .text(v.value)
            );
            });
        });
}
function settaValoreRegione(province){
    var nomeRegione = $(province).find('option:selected').parent().attr('label');    
    var regione = $('select[data-ajax-regione]');
    var optRegione = regione.find('option:contains("'+nomeRegione+'")');
    if(!optRegione){
        return;
    }
    var regioneValue = optRegione.val()
    debugger;
    regione.val(regioneValue);
    App.initAjax();
}
function initChoiceComuni() {
var province = $('select[data-ajax-key]');
settaValoriComuni(province);
province.change(function (event) {    
    settaValoriComuni(province);
    settaValoreRegione(event.target);
});

}

$(document).ready(function () {
initChoiceComuni();
});