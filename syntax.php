<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * S5 Plugin: Display a Wiki page as S5 slideshow presentation
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
class syntax_plugin_s5 extends SyntaxPlugin
{
    /** @inheritdoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritdoc */
    public function getPType()
    {
        return 'normal';
    }

    /** @inheritdoc */
    public function getSort()
    {
        return 800;
    }

    /** @inheritdoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~SLIDESHOW[^~]*~~', $mode, 'plugin_s5');
    }

    /** @inheritdoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        if ($match != '~~SLIDESHOW~~') return [trim(substr($match, 11, -2))];
        return [];
    }

    /** @inheritdoc */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        global $ID;
        if ($format != 'xhtml') return false;

        $renderer->doc .= '<a href="' . exportlink($ID, 's5', count($data) ? ['s5theme' => $data[0]] : null) . '" title="' . $this->getLang('view') . '">';
        $renderer->doc .= '<img src="' . DOKU_BASE . 'lib/plugins/s5/screen.gif" align="right" alt="' . $this->getLang('view') . '" width="48" height="48" />';
        $renderer->doc .= '</a>';
        return true;
    }
}
