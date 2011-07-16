<?php
/**
 * PlantUML-Plugin: Parses plantuml blocks to render images and html
 *
 * @license GPL v2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Andreone
 * @author  Willi Schönborn <w.schoenborn@googlemail.com>
 */

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
require_once(DOKU_INC . 'inc/init.php');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_plantuml extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType() {
        return 'substition';
    }

    /**
     * Where to sort in?
     */
    function getSort() {
        return 200;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<uml.*?>\n.*?\n</uml>', $mode, 'plugin_plantuml');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        // echo "handle: state=$state<br>";
        // echo "handle: match=$match<br>";
        // echo "handle: pos=$pos<br>";

        $info = $this->getInfo();

        // prepare default data
        $return = array(
            'width' => 0,
            'height' => 0,
            'title' => 'PlantUML Graph',
            'align' => '',
            'version' => $info['date'],
        );

        // prepare input
        $lines = explode("\n", $match);
        $conf = array_shift($lines);
        array_pop($lines);

        // alignment
        if (preg_match('/\b(left|center|right)\b/i', $conf, $matches)) {
            $return['align'] = $matches[1];
        }

        // size
        if (preg_match('/\b(\d+)x(\d+)\b/', $conf, $matches)) {
            $return['width'] = $matches[1];
            $return['height'] = $matches[2];
        } else {
            if (preg_match('/\b(?:width|w)=([0-9]+)(%?)/i', $conf, $matches)) {
                $return['width'] = $matches[1];
                $return['percent'] = $matches[2];
            }
            if (preg_match('/\b(?:height|h)=([0-9]+)\b/i', $conf, $matches)) {
                $return['height'] = $matches[1];
            }
        }

        // title
        if (preg_match('/\b(?:title|t)=(\w+)\b/i', $conf, $matches)) {
            // single word titles
            $return['title'] = $matches[1];
        } else if (preg_match('/(?:title|t)="([\w+\s+]+)"/i', $conf, $matches)) {
            // multi word titles
            $return['title'] = $matches[1];
        }

        $input = join("\n", $lines);
        $return['md5'] = md5($input);

        io_saveFile($this->_cachename($return, 'txt'), "@startuml\n$input\n@enduml");

        return $return;
    }

    /**
     * Cache file is based on parameters that influence the result image
     */
    function _cachename($data, $ext){
        unset($data['width']);
        unset($data['height']);
        unset($data['align']);
        unset($data['title']);
        return getcachename(join('x', array_values($data)), ".plantuml.$ext");
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if ($mode == 'xhtml') {
            $img = DOKU_BASE . 'lib/plugins/plantuml/img.php?' . buildURLParams($data);
            
            if($data['width']) {
                $temp = $data['width'];
                $data['width'] = 0;
                $img_unresized = DOKU_BASE . 'lib/plugins/plantuml/img.php?' . buildURLParams($data);
                $data['width'] = $temp;
            } else {
                $img_unresized = $img;
            }
            
            $renderer->doc .= '<a title="' . $data['title'] . '" class="media" href="' . $img_unresized . '">';
            $renderer->doc .= '<img src="' . $img . '" class="media' . $data['align'] . '" title="' . $data['title'] . '" alt="' . $data['title'] .  '"';
            if ($data['width']) {
                $renderer->doc .= ' width="' . $data['width'] . $data['percent'] . '"';
            }
            if ($data['height']) {
                $renderer->doc .= ' height="' . $data['height'] . '"';
            }
            if ($data['align'] == 'left') {
                $renderer-> doc .= ' align="left"';
            }
            if ($data['align'] == 'right') {
                $renderer->doc .= ' align="right"';
            }
            $renderer->doc .= '/></a>';
            return true;
        } else if ($mode == 'odt') {
            $src = $this->_imgfile($data);
            $renderer->_odtAddImage($src, $data['width'], $data['height'], $data['align']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return path to the rendered image on our local system
     * Note this is also called by img.php
     */
    function _imgfile($data) {
        $cache = $this->_cachename($data, 'png');

        // create the file if needed
        if (!file_exists($cache)) {
            $in = $this->_cachename($data, 'txt');
            if ($this->getConf('render_local') == '0' && $this->getConf('remote_url')) {
                $ok = $this->_remote($data, $in, $cache);
            } else if ($this->getConf('render_local') == '1' && $this->getConf('java')) {
                $ok = $this->_local($data, $in, $cache);
            } else {
                return false;
            }

            if (!$ok) return false;
            clearstatcache();
        }

        if ($data['width'] && $data['percent'] != '%') {
            $cache = media_resize_image($cache, 'png', $data['width'], $data['height']);
        }

        return file_exists($cache) ? $cache : false; 
    }

    /**
     * Render the output remotely at plantuml.no-ip.org
     */
    function _remote($data, $in, $out) {
        if (!file_exists($in)) {
            dbglog($in, 'No such plantuml input file');
            return false;
        }

        $http = new DokuHTTPClient();
        $http->timeout = 30;

        $remote_url = $this->getConf('remote_url');
        // strip trailing "/" if present
        $base_url = preg_replace('/(.+?)\/$/', '$1', $remote_url);

        $java = $this->getConf('java');
        if ($java) {
            // use url compression if java is available
            $jar = $this->getConf('jar');
            $jar = realpath($jar);
            $jar = escapeshellarg($jar);

            $command = $java;
            $command .= ' -Djava.awt.headless=true';
            $command .= ' -Dfile.encoding=UTF-8';
            $command .= " -jar $jar";
            $command .= ' -charset UTF-8';
            $command .= ' -encodeurl';
            $command .= ' ' . escapeshellarg($in);
            $command .= ' 2>&1';

            $encoded = exec($command, $output, $return_value);

            if ($return_value == 0) {
               $url = "$base_url/image/$encoded"; 
            } else {
                dbglog(join("\n", $output), "Encoding url failed: $command");
                return false;
            }
        } else {
            $uml = io_readFile($in);
            // remove @startuml and @enduml, as they are not required by the webservice 
            $uml = str_replace("@startuml\n", '', $uml);
            $uml = str_replace("\n@enduml", '', $uml);
            $uml = str_replace("\n", '/', $uml);
            $uml = urlencode($uml);
            // decode encoded slashes (or plantuml server won't understand)
            $uml = str_replace('%2F', '/', $uml);

            $url = "$base_url/startuml/$uml";
        }

        $img = $http->get($url);
        return $img ? io_saveFile($out, $img) : false;
    }

    /**
     * Render the output locally using the plantuml.jar
     */
    function _local($data, $in, $out) {
        if (!file_exists($in)) {
            dbglog($in, 'No such plantuml input file');
            return false;
        }
        
        $java = $this->getConf('java');
        $jar = $this->getConf('jar');
        $jar = realpath($jar);
        $jar = escapeshellarg($jar);

        // we are not specifying the output here, because plantuml will generate a file with the same
        // name as the input but with .png extension, which is exactly what we want
        $command = $java;
        $command .= ' -Djava.awt.headless=true';
        $command .= ' -Dfile.encoding=UTF-8';
        $command .= " -jar $jar";
        $command .= ' -charset UTF-8';
        $command .= ' ' . escapeshellarg($in);
        $command .= ' 2>&1';

        exec($command, $output, $return_value);

        if ($return_value == 0) {
            return true;
        } else {
            dbglog(join("\n", $output), "PlantUML execution failed: $command");
            return false;
        }
    }
    
    /**
     * Dumps a message in a log file (named dokuwiki_plantuml.log and located in the Dokuwidi's cache directory)
     */
    function _log($text) {
        global $conf;
        $hFile = fopen($conf['cachedir'].'/dokuwiki_plantuml.log', a);
        if(hFile) {
            fwrite($hFile, $text . "\r\n");
            fclose($hFile);
        }
    }
}
