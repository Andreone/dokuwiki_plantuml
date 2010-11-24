<?php
/**
 * PlantUML-Plugin: Add a toolbar button to insert a plantuml block
 *
 * @license   GPL v3 (http://www.gnu.org/licenses/gpl.html)
 * @author    Andreone
 * @version 0.2
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'action.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_plantuml extends DokuWiki_Action_Plugin {
 
    /**
     * Register the event handler
     */
    function register(&$controller) {
        if($this->getConf('toolbar_button_enabled') == '1')
            $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
    }
 
    /**
     * Inserts the toolbar button
     */
    function insert_button(& $event, $param) {
        $sample .= PHP_EOL.'<you can specify a display size by putting w=N[%] h=N[%] after the start tag (on the same line separated with a space)'.PHP_EOL;
        $sample .= 'title <put the title of the schema here>'.PHP_EOL;
        $sample .= PHP_EOL.'put the implementation of the schema here'.PHP_EOL;
        $sample .= 'checkout http://plantuml.sourceforge.net for language details'.PHP_EOL;

         $event->data[] = array (
            'type' => 'format',
            'title' => $this->getLang('plantuml_tooltip'),
            'icon' => '../../plugins/plantuml/'.$this->getConf('toolbar_button_icon'),
            'open' => '@startuml',
            'close' => '@enduml',
            'sample' => $sample,
        );
    }
}
