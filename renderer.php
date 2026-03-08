<?php

/**
 * The Renderer
 */
class renderer_plugin_s5 extends Doku_Renderer_xhtml
{
    protected $slideopen = false;
    protected $base = '';
    protected $tpl = '';

    /**
     * the format we produce
     */
    public function getFormat()
    {
        // this should be 's5' usally, but we inherit from the xhtml renderer
        // and produce XHTML as well, so we can gain magically compatibility
        // by saying we're the 'xhtml' renderer here.
        return 'xhtml';
    }


    /**
     * Initialize the rendering
     */
    public function document_start()
    {
        global $ID;

        // call the parent
        parent::document_start();

        // store the content type headers in metadata
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8'
        ];
        p_set_metadata($ID, ['format' => ['s5' => $headers] ]);
        $this->base = DOKU_BASE . 'lib/plugins/s5/ui/';
        $this->tpl  = $_GET['s5theme'] ?? $this->getConf('template');
        $this->tpl = preg_replace('/[^a-z0-9_-]+/', '', $this->tpl); // clean user provided path
    }

    /**
     * Print the header of the page
     *
     * Gets called when the very first H1 header is discovered. It includes
     * all the S5 CSS and JavaScript magic
     */
    protected function s5_init($title)
    {
        global $conf;
        global $lang;
        global $INFO;
        global $ID;

        //throw away any previous content
        $this->doc = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $conf['lang'] . '"
 lang="' . $conf['lang'] . '" dir="' . $lang['direction'] . '">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>' . hsc($title) . '</title>
<!-- metadata -->
<meta name="generator" content="S5" />
<meta name="version" content="S5 1.1" />
<!-- configuration parameters -->
<meta name="defaultView" content="slideshow" />
<meta name="controlVis" content="hidden" />
<!-- style sheet links -->
<link rel="stylesheet" href="' . DOKU_BASE . 'lib/styles/all.css" type="text/css" media="screen" />
<link rel="stylesheet" href="' . DOKU_BASE . 'lib/styles/screen.css" type="text/css" media="screen" />
<link rel="stylesheet" href="' . $this->base . $this->tpl . '/slides.css" type="text/css" media="projection" id="slideProj" />
<link rel="stylesheet" href="' . $this->base . 'default/outline.css" type="text/css" media="screen" id="outlineStyle" />
<link rel="stylesheet" href="' . $this->base . 'default/print.css" type="text/css" media="print" id="slidePrint" />
<link rel="stylesheet" href="' . $this->base . 'default/opera.css" type="text/css" media="projection" id="operaFix" />
<!-- S5 JS -->
<script src="' . $this->base . 'default/slides.js" type="text/javascript"></script>
</head>
<body>
<div class="layout">
<div id="controls"><!-- DO NOT EDIT --></div>
<div id="currentSlide"><!-- DO NOT EDIT --></div>
<div id="header"></div>
<div id="footer">
<h1>' . tpl_pagetitle($ID, true) . '</h1>
<h2>' . hsc($conf['title']) . ' &#8226; ' . strftime($conf['dformat'], $INFO['lastmod']) . '</h2>
</div>

</div>
<div class="presentation">
';
    }

    /**
     * Closes the document
     */
    public function document_end()
    {
        // we don't care for footnotes and toc
        // but cleanup is nice
        $this->doc = preg_replace('#<p>\s*</p>#', '', $this->doc);

        if ($this->slideopen) {
            $this->doc .= '</div>' . DOKU_LF; //close previous slide
        }
        $this->doc .= '</div>
                       </body>
                       </html>';
    }

    /**
     * This is what creates new slides
     *
     * A new slide is started for each H2 header
     */
    public function header($text, $level, $pos, $returnonly = false)
    {
        if ($level == 1) {
            if (!$this->slideopen) {
                $this->s5_init($text); // this is the first slide
                $level = 2;
            } else {
                return;
            }
        }

        if ($level == 2) {
            if ($this->slideopen) {
                $this->doc .= '</div>' . DOKU_LF; //close previous slide
            }
            $this->doc .= '<div class="slide">' . DOKU_LF;
            $this->slideopen = true;
        }
        $this->doc .= '<h' . ($level - 1) . '>';
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= '</h' . ($level - 1) . '>' . DOKU_LF;
    }

    /**
     * Top-Level Sections are slides
     */
    public function section_open($level)
    {
        if ($level < 3) {
            $this->doc .= '<div class="slidecontent">' . DOKU_LF;
        } else {
            $this->doc .= '<div>' . DOKU_LF;
        }
        // we don't use it
    }

    /**
     * Throw away footnote
     */
    public function footnote_close()
    {
        $this->doc = $this->store;
        $this->store = '';
    }

    /**
     * No acronyms in a presentation
     */
    public function acronym($acronym)
    {
        $this->doc .= $this->_xmlEntities($acronym);
    }

    /**
     * A line stops the slide and start the handout section
     */
    public function hr()
    {
        $this->doc .= '</div>' . DOKU_LF;
        $this->doc .= '<div class="handout">' . DOKU_LF;
    }
}
