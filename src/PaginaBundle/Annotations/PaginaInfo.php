<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 23/12/15
 * Time: 12:04
 */

namespace PaginaBundle\Annotations;


/**
 * Questa classe serve come annotazione per i controller in modo da generare automaticamente il titolo della pagina
 *
 * Il titolo e il sottotitolo sono abbastanza ovvi
 *
 * Esempio di annotazione da mettere nel controller
 * "@PaginaInfo(titolo="Titolo della pagina",sottoTitolo="sotto titolo della pagina", elemento1="Home,home", elemento2="Pagina,home")"
 *
 *
 * @Annotation
 */

class PaginaInfo
{
    public $titolo;
    public $sottoTitolo;

}