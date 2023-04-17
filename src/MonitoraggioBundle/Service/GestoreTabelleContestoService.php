<?php
namespace MonitoraggioBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MonitoraggioBundle\Entity\ElencoTabelleContesto;


/**
 * Description of GestoreElencoTabelleContestoService
 *
 * @author lfontana
 */
class GestoreTabelleContestoService
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ElencoTabelleContesto|null $tabella
     * @return object|IGestoreTabelleContesto
     * @throws \Exception
     */

    public function getGestore(ElencoTabelleContesto $tabella = null)
    {
        $classe = 'MonitoraggioBundle\GestoriTabelleContesto\\' . (is_null($tabella) ? 'Elenco' : GestoreTabelleContestoBase::getSuffisso($tabella));
        if (class_exists($classe)) {
            return new $classe($this->container, $tabella);
        }
        return new GestoreTabelleContestoBase($this->container, $tabella);
    }
/*
    public function getStruttureConstraints(\MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazione $configurazione)
    {
        $namespace = 'MonitoraggioBundle\Constraints\\';
        $className = (new \ReflectionObject($configurazione))->getShortName();
        switch ($className) {
            case 'MonitoraggioEsportazioneProgetto' :
                $namespace .= 'Progetto';
                break;

            case 'MonitoraggioEsportazioneProcedura' :
                $namespace .= 'Procedura';
                break;

            case 'MonitoraggioEsportazioneTrasferimento' :
                $namespace .= 'Trasferimento';
                break;

            default :
                throw new \Exception('Oggetto non gestito da constraint');
                break;
        }

        $files = scandir(self::getNamespaceDirectory($namespace));

        $classes = \array_map(function ($file) use ($namespace) {
            return $namespace . '\\' . str_replace('.php', '', $file);
        }, $files);

        $classi = \array_filter($classes, function ($possibleClass) {
            return class_exists($possibleClass);
        });

        return \array_map(function($classe) use($namespace){
            $classeDaIstanziare = '\\'.$namespace .'\\' . $classe;
            return new $classeDaIstanziare();
        }, $classi);
    }
    */
}
