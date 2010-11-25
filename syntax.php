<?php
/**
 * PlantUML-Plugin: Parses plantuml blocks to render images
 *
 * @license	GPL v3 (http://www.gnu.org/licenses/gpl.html)
 * @author	Andreone
 */

if (!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
require_once(DOKU_INC.'inc/init.php');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_plantuml extends DokuWiki_Syntax_Plugin {

    private $width;
    private $height;
    private $storedir;
    private $jar_path;
    
    function __construct() {
        // parent::__construct();
        global $conf;
        $this->storedir = $conf['mediadir'] . '/plantuml/';

        // when the path of the plantuml jar folder is not set, asssume the jar is in the plugin folder
        $this->jar_path = trim($this->getConf('jar_path'));
        if(!strlen($this->jar_path)) {
            $this->jar_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'plantuml_jar';
        }
    }	

    /**
     * What kind of syntax are we?
     */
    function getType() {
        return 'protected';
    }

    /**
     * Where to sort in?
     */
    function getSort() {
        return 999;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('@startuml.*?$(?=.*?@enduml)',$mode,'plugin_plantuml');
    }

    function postConnect() {
        $this->Lexer->addExitPattern('@enduml','plugin_plantuml');
    }

    /**     * Handle the match
     */

    function handle($match, $state, $pos, &$handler) {
        global $conf;
        // echo "handle: state=$state<br>";
        // echo "handle: match=$match<br>";
        // echo "handle: pos=$pos<br>";
        switch ($state) {
          case DOKU_LEXER_ENTER :
            if ( !is_dir($this->storedir) )
                io_mkdir_p($this->storedir); //Using dokuwiki framework
            return array($state, $match);
 
          case DOKU_LEXER_UNMATCHED :
            if(preg_match('/^title\s+(.*?)$/m', $match, $matches) > 0) {
                $title = htmlspecialchars($matches[1]);
            }
            else
                $title = 'PlantUML Graph';
            
            $hash = md5(serialize($match));
            $url = ml('plantuml:'.$hash.'.png'); //Using dokuwiki framework
            if (!file_exists($this->getStoredFileName($hash))) {
                $data = '@startuml' . PHP_EOL . $match . PHP_EOL . '@enduml';
                if(!$this->createImage($this->storedir, $hash, $data)) {
                    return array($state, '**ERROR RENDERING GRAPH**');
                }
            }
            
            return array($state, $url, $title); 

          case DOKU_LEXER_EXIT :
            return array($state, '');
        }
        return array();
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data)
    {
        if ($mode == 'xhtml') {
            $state = $data[0];
            // echo "render: state=$state<br>";
            // echo 'render: data='; var_dump($data); echo'<br>';
            switch ($state) {
              case DOKU_LEXER_ENTER :
                $match = $data[1];
                $this->height = '';
                $this->width = '';
                $this->interpretArguments(explode(' ', $match));
               break;
 
              case DOKU_LEXER_UNMATCHED :
                $url = $data[1];
                $title = $data[2];
                
                $renderer->doc .= '<a title="' . $title . '" class="media" href="' . $url . '">' .
                                  '<img title="' . $title . '" class="media"';
                if($this->width != '' || $this->height != '') {
                    if($this->width != '') {
                        if(strpos($this->width, '%')===FALSE)
                            $replace = 'w=' . $this->width;
                        $renderer->doc .= ' width="' .  $this->width . '"';
                    }
                    if($this->height != '') {
                        if(strpos($this->height, '%')===FALSE)
                            $replace .= ($replace == '' ? 'h=' : '+h=') . $this->height;
                        $renderer->doc .= ' height="' .  $this->height . '"';
                    }
                    // let php nicely resize the png (better quality than the navigator)
                    $renderer->doc .= ' src="' . str_replace('?', '?' . $replace . '&', $url) . '"';
                }
                else {
                    $renderer->doc .= ' src="' . $url . '"';
                }
                $renderer->doc .= '/></a>'; 
                break;
              
              case DOKU_LEXER_EXIT :
                break;
            }
            return true;
        }
        return false;
    }

    function createImage($destdir, $hash, &$data) 
    {
        $tempFileName = sys_get_temp_dir() . $hash . '.txt';
        $h = fopen($tempFileName, "w+");
        fwrite($h, $data);
        fclose($h);
        $retval = exec('java -Djava.awt.headless=true -jar '.$this->jar_path.' -o '.$destdir.' '.$tempFileName);
        unlink($tempFileName);
        return $retval == 0;
    }
    
    // get custom width and height
    function interpretArguments($args)
    {
        foreach($args as $arg) {
            $elements = explode('=', $arg);
            if(count($elements) < 2)
                continue;
            
            switch($elements[0])
            {
                case "w" : $this->width = htmlspecialchars($elements[1]); break;
                case "h" : $this->height = htmlspecialchars($elements[1]); break;
                default : break;
            }
        }
    }
    
    // returns the full path to a file from its hash value
    function getStoredFileName($hash) {
        global $conf;
        return $conf['mediadir'] . '/plantuml/'. $hash . '.png';
    }
}
