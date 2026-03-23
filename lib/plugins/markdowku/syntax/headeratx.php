<?php
/*
 * Header in ATX style, i.e. '# Header1', '## Header2', ...
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_markdowku_headeratx extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'baseonly'; }
    function getPType() { return 'block'; }
    function getSort()  { return 49; }
    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'protected');
    }
  
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\n\#{1,6}[ \t]*.+?[ \t]*\#*(?=\n+)',
            'base',
            'plugin_markdowku_headeratx');
    }
  
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $conf;

        $title = trim($match);
        $level = strspn($title, '#');
        $title = trim($title, '#');
        $title = trim($title);

        if ($level < 1)
            $level = 1;
        elseif ($level > 6)
            $level = 6;

        // HACK: Change level if task not done (RED)
        if (strstr($title,'- [_]')){
            $level=5;
        }
        // HACK: Change level if task is in progress (RED)
        if (strstr($title,'- [>]')){
            $level=5;
        }
        // HACK: Change level if task done (GREEN)
        if (strstr($title,'- [x]')){
            $level=6;
        }
        // HACK: Change level if task cancelled (BLACK)
        if (strstr($title,'- [-]')){
            $level=6;
        }        

        // HACK: Change some tasks string for emojis
        $title=str_replace('- [x]','- ✅',$title);
        $title=str_replace('- [>]','- 🟧',$title);
        $title=str_replace('- [ ]','- 🔲',$title);
        $title=str_replace('- [_]','- 🔲',$title);
        $title=str_replace('- [-]','- ❌',$title);
        $title=str_replace('- (x)','- 🟢',$title);
        $title=str_replace('- (>)','- 🟠',$title);
        $title=str_replace('- (-)','- 🔴',$title);
        $title=str_replace('- (_)','- ⭕',$title);


        // FEATURE: removing bold from header with ** from artificial inteligence markdown copy and paste
        $title = preg_replace('/\*\*(.+?)\*\*/', '"$1"', $title);
        // FEATURE: removing italics from header with * from artificial inteligence markdown copy and paste
        $title = preg_replace('/\*(.+?)\*/', '"$1"', $title);
        // FEATURE: removing underline from header with __ from artificial inteligence markdown copy and paste
        $title =  preg_replace('/__(.+?)__/', '"$1"', $title);



        // THIS IS NOT WORKING BUT WE LEAVE FOR FUTURE IMPROVEMENTS

        // // FEATURE: Adminiting markdown bold in header with **
        // $title = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $title);

        // // FEATURE: Adminiting markdown italics in header with *
        // $title = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $title);

        // // FEATURE: Adminiting underline in header with __
        // $title =  preg_replace('/__(.+?)__/', '<u>$1</u>', $title);


        if ($handler->getStatus('section'))
            $handler->_addCall('section_close', array(), $pos);
        if ($level <= $conf['maxseclevel']) {
            $handler->setStatus('section_edit_start', $pos);
            $handler->setStatus('section_edit_level', $level);
            $handler->setStatus('section_edit_title', $title);
        }
        $handler->_addCall('header', array($title, $level, $pos), $pos);
        $handler->_addCall('section_open', array($level), $pos);
        $handler->setStatus('section', true);

        return true;
    }
  
    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
