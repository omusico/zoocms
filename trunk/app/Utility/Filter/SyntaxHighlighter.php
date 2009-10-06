<?php
/**
 * @package Utility
 * @subpackage Filter
 * 
 */


/**
 * @package Utility
 * @subpackage Filter
 */

class Utility_Filter_SyntaxHighlighter {
    /**
     * Alias lookup table
     *
     * @var array
     */
    private $alias = array('bash' => 'Bash',
                            'shell' => 'Bash',
                            'c-sharp' => 'CSharp',
                            'csharp' => 'CSharp',
                            'cpp' => "Cpp",
                            'c' => "Cpp",
                            'css' => "Css",
                            'delphi' => "Delphi",
                            'pas' => "Delphi",
                            'pascal' => "Delphi",
                            'diff' => "Diff",
                            'patch' => "Diff",
                            'groovy' => "Groovy",
                            'js' => "JScript",
                            'jscript' => "JScript",
                            'javascript' => "JScript",
                            'java' => "Java",
                            'perl' => "Perl",
                            'pl' => "Perl",
                            'php' => "Php",
                            'plain' => "Plain",
                            'text' => "Plain",
                            'py' => "Python",
                            'python' => "Python",
                            'rails' => "Ruby",
                            'ror' => "Ruby",
                            'ruby' => "Ruby",
                            'scala' => "Scala",
                            'sql' => "Sql",
                            'vb' => "Vb",
                            'vbnet' => "Vb",
                            'xml' => "Xml",
                            'xhtml' => "Xml",
                            'xslt' => "Xml",
                            'html' => "Xml",
                            'xhtml' => "Xml");

    /**
     *
     * @param string $text text to parse
     * @return string
     */
    public function filter($text) {
        $pattern = "/\[code=(['\"]?)([^\"'<>]*)\\1](.*)\[\/code\]/sU";

        $matches = array();
        preg_match_all($pattern, $text, $matches);
        if (count($matches) > 0) {

            $langs = array();
            $matches[2] = array_unique($matches[2]);
            foreach (array_keys($matches[0]) as $i) {
                $langs[] = $matches[2][$i];

                $text = str_replace($matches[0][$i], '<pre class="brush: '.strtolower($matches[2][$i]).'">'.htmlspecialchars($matches[3][$i]).'</pre>', $text);
            }

            $this->addScriptsAndStyles($langs);
        }
        // Remove code start tag where end tag is truncated
        $last_bracket = strrpos($text, "[");
        if ($last_bracket > 0) {
            $text = substr($text, 0, $last_bracket);
        }

        return $text;
    }

    /**
     * Add scripts and styling to <head>
     *
     * @todo Only add CSS once and JS once per language
     * @param array $langs
     * @return void
     */
    protected function addScriptsAndStyles($langs = array()) {
        if (count($langs) > 0) {
            $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
            $view->headLink()->appendStylesheet('/js/SyntaxHighlighter/styles/shCore.css');
            $view->headLink()->appendStylesheet('/js/SyntaxHighlighter/styles/shThemeDefault.css');
            $view->headScript()->appendFile('/js/SyntaxHighlighter/scripts/shCore.js', 'text/javascript');
            foreach ($langs as $lang) {
                if ($lang = $this->getAlias($lang)) {
                    $view->headScript()->appendFile('/js/SyntaxHighlighter/scripts/shBrush'.ucfirst($lang).'.js',
                                                    'text/javascript');
                }
            }
            $view->headScript()->appendScript("
                                 SyntaxHighlighter.all();
                                ", "text/javascript");
        }
        return;
    }

    /**
     * Get alias for a language
     *
     * @param string $lang
     * @return string|false
     */
    private function getAlias($lang) {
        if (isset($this->alias[strtolower($lang)])) {
            return $this->alias[strtolower($lang)];
        }
        return false;
    }
}