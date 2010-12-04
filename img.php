<?php
/**
 * @license GPL v2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Willi Schönborn (w.schoenborn@googlemail.com)
 */

if (!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/../../../');
define('NOSESSION', true);
require_once(DOKU_INC . 'inc/init.php');

$data = $_REQUEST;
$plugin = plugin_load('syntax', 'plantuml');
$cache  = $plugin->_imgfile($data);

if ($cache) {
    header('Content-Type: image/png;');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + max($conf['cachetime'], 3600)) . ' GMT');
    header('Cache-Control: public, proxy-revalidate, no-transform, max-age=' . max($conf['cachetime'], 3600));
    header('Pragma: public');
    http_conditionalRequest($time);
    echo io_readFile($cache, false);
} else {
    header('HTTP/1.0 404 Not Found');
    header('Content-Type: image/png');
    echo io_readFile('res/file-broken/file-broken.png', false);
}
