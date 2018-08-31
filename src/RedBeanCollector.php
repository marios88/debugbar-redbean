<?php
namespace Filisko\DebugBar\DataCollector;

class RedBeanCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable, \DebugBar\DataCollector\AssetProvider
{
    /**
     * Whether to show or not '--keep-cache' in your queries.
     * @var boolean
     */
    public static $showKeepCache = false;

    /**
     * Logger must implement RedBean's Logger interface.
     * @var \RedBeanPHP\Logger
     */
    protected $logger;

    protected $name;

    /**
     * Set RedBean's logger
     * @param \RedBeanPHP\Logger $logger
     */
    public function __construct(\RedBeanPHP\Logger $logger,$name = 'redbean')
    {
        $this->logger = $logger;
        $this->name = $name;
    }

    /**
     * Collect all the executed queries by now.
     */
    public function collect()
    {
        // Get all SQL output
        $queries = [];
        $totalExecTime = 0;

        $output = $this->logger->getLogs();
        $outputCount = count($output);
        $queries = array();
        for($i=0;$i<$outputCount;$i+=4){
            if (! self::$showKeepCache) {
                $output[$i] = str_replace('-- keep-cache', '', $output[$i]);
                $output[($i+1)] = str_replace('-- keep-cache', '', $output[($i+1)]);
            }
            if($this->name == 'redbean'){
                $queries[] = array(
                    // 1 space maximum and no HTML included tags by RedBean
                    'sql' => htmlspecialchars(preg_replace('!\s+!', ' ', $output[$i])),
                    'connection' => $output[($i+2)],
                    'duration' => $output[($i+3)],
                    'duration_str' => $this->formatDuration($output[($i+3)])
                );
            }else{
                $queries[] = array(
                    // 1 space maximum and no HTML included tags by RedBean
                    'sql' => htmlspecialchars(preg_replace('!\s+!', ' ', $output[($i+1)])),
                );
            }
            $totalExecTime += $output[($i+3)];
        }

        return array(
            'nb_statements' => count($queries),
            'accumulated_duration' => $totalExecTime,
            'accumulated_duration_str' => $this->formatDuration($totalExecTime),
            'statements' => $queries
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWidgets()
    {
        return array(
            $this->name => array(
                "icon" => "inbox",
                "widget" => "PhpDebugBar.Widgets.SQLQueriesWidget",
                "map" => $this->name,
                "default" => "[]"
            ),
            $this->name.":badge" => array(
                "map" => $this->name.".nb_statements",
                "default" => 0
            )
        );
    }

    public function getAssets()
    {
        return array(
            'css' => 'widgets/sqlqueries/widget.css',
            'js' => 'widgets/sqlqueries/widget.js'
        );
    }
}
