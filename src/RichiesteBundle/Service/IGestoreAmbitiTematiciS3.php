<?php
namespace RichiesteBundle\Service;

use RichiesteBundle\Entity\AmbitoTematicoS3Proponente;
use RichiesteBundle\Entity\Richiesta;

interface IGestoreAmbitiTematiciS3
{
	public function gestioneAmbitiTematiciS3(Richiesta $richiesta, array $opzioni =[]);
	public function aggiungiAmbitoTematicoS3(Richiesta $richiesta, array $opzioni =[]);
	public function validaAmbitiTematiciS3(int $id_richiesta);
	public function eliminaAmbitoTematicoS3Proponente(int $id_ambito_tematico_s3_proponente);
    public function gestioneDescrittori(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente, array $opzioni =[]);
    public function aggiungiDescrittoreAmbitoTematicoS3(AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente, array $opzioni =[]);
    public function eliminaDescrittoreAmbitoTematicoS3(int $id_ambito_tematico_s3_proponente, int $id_descrittore);
    public function modificaDescrittoreAmbitoTematicoS3(int $id_ambito_tematico_s3_proponente, int $id_descrittore);
}
