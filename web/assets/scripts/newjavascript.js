function prova() {
    alert('work');
}

var ajax_load = "<img src='{{asset('assets/img/ajax-loading.gif')}}' alt='loading' />";

jQuery(document).ready(function() {
    PortletDraggable.init(); // initlayout and core plugins
    UIKnob.init();
});

jQuery(document).ready(function() {
    aggiornaErrori();
    aggiornaProdotti();
});

function resettaErrori(tipo) {
    if (tipo == null)
        tipo = "all";
    bootbox.confirm("Vuoi veramente resettare gli errori?", function(result) {
        if (result != null) {
            var path = "{{path('macchina-errori-reset', { 'id': entity.id })}}" + "/" + tipo;
            $.ajax({
                url: path,
                type: 'GET',
                error: function(msg) {
                    $('#macchina-segnalazioni').html("<p>Un errore e' accaduto nel invio della richiesta");
                },
                success: function(res) {
                    aggiornaErrori('all');
                }
            });
        }
    });
}



function aggiornaErrori() {
    if (tipo == null)
        tipo = "all";

    $('#macchina-segnalazioni').html(ajax_load);
    var path = "{{path('macchina_errori_elenco', { 'id': entity.id })}}" + "/" + tipo;
    $.ajax({
        url: path,
        type: 'GET',
        error: function(msg) {
            $('#macchina-segnalazioni').html("<p>Un errore e' accaduto nel recupero dei dati");
        },
        success: function(res) {
            var timeline = '<ul class="timeline">';
            var errori = eval(res);
            var colore = "blue";
            var icon = "fa-comment-o";
            for (indice in errori) {
                switch (errori[indice].type) {
                    case 'Emergency':
                        colore = "red";
                        icon = "fa-exclamation-circle";
                        break;
                    case 'Disable':
                        colore = "purple";
                        icon = "fa-minus-circle";
                        break;
                    case 'Malfunction':
                        colore = "red";
                        icon = "fa-bullhorn";
                        break;
                    case 'Stop':
                        colore = "red";
                        icon = "fa-exclamation-circle";
                        break;
                    case 'Warning':
                        colore = "yellow";
                        icon = "fa-exclamation-triangle";
                        break;
                    case 'Segnalation':
                        colore = "yellow";
                        icon = "fa-info-circle";
                        break;
                    case 'Alert':
                        colore = "grey";
                        icon = "fa-user";
                        break;
                    default:
                        colore = "blue";
                        icon = "fa-comment-o";
                }
                timeline += '<li class="timeline-' + colore + '">';
                var dataOra = errori[indice].date.split(" ");
                timeline += '<div class="timeline-time">';
                if (dataOra.length != 2) {
                    timeline += '<span class="date">-</span>';
                    timeline += '<span class="time">-</span>';
                } else {
                    timeline += '<span class="date">' + dataOra[0] + '</span>';
                    timeline += '<span class="time">' + dataOra[1] + '</span>';
                }
                timeline += '</div>';
                timeline += '<div class="timeline-icon"><i class="fa ' + icon + '"></i></div>';
                timeline += '<div class="timeline-body">';
                timeline += '<h4>' + errori[indice].number + " | " + errori[indice].entity + '</h4>';
                timeline += '<div class="pull-right">' + errori[indice].type + '</div>';
                timeline += '<div class="timeline-content">';
                timeline += errori[indice].desc;
                timeline += '</div>';
                timeline += '</div>';
                timeline += "</li>";
            }
            timeline += "</ul>";
            $('#macchina-segnalazioni').html(timeline);
        }
    });
}

function aggiornaProdotti() {
    //$('#prodotti-slots').html(ajax_load);
    $.ajax({
        url: "{{path('macchina_slots_dettagli', { 'id': entity.id })}}",
        type: 'GET',
        error: function(res) {
            $('#prodotti-slots').html("<p>Un errore e' accaduto nel recupero dei dati");
        },
        success: function(res) {
            var slots = eval(res);
            var path = "{{path('macchina_slot_dettaglio', { 'id': entity.id, 'slot':"
                    tmp
            " })}}";
                    for (number in slots) {
                var knob = '<h4><a href="' + path.replace("tmp", number.toString()) + '" data-target="#ajax" data-toggle="modal">' + slots[number].id + ' <i class="m-icon-swapright"></i></a></h4>';
                knob += '<h6>' + slots[number].name + '</h6>';
                knob += '<input class="knob centered" data-min="0" data-max="12" data-readOnly="true" data-width="100" data-height="100" ';
                knob += 'value="' + slots[number].quantity + '" ';
                if (slots[number].quantity <= 2) {
                    knob += 'data-fgColor="#e02222"';
                } else if (slots[number].quantity > 2 && slots[number].quantity < 5) {
                    knob += 'data-fgColor="#ffb848"';
                } else {
                    knob += 'data-fgColor="#35aa47"';
                }
                knob += '>';
                $('#prodotto-slot-' + number).html(knob);
            }
            UIKnob.init();
        }
    });
}