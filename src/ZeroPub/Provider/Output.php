<?php

namespace ZeroPub\Provider;

/**
 * Class Output
 *
 * @package ZeroPub
 */
class Output implements OutputInterface
{
    /**
     * @param array $params Paramètres des scripts
     *
     * @return string
     */
    public function getScriptsHtml(array $params)
    {
        return <<<HTML
<script type="text/javascript" src="{$params['js']['honeypot']}"></script>
<script type="text/javascript" src="{$params['js']['main']}"></script>
<link rel="stylesheet" href="{$params['css']['main']}"/>
HTML;
    }

    /**
     * @param array $params Paramètres de la zone
     *
     * @return string
     */
    public function getAdHtml(array $params)
    {
        $html = '<div ';

        if (null !== $params['class']) {

            $html .= 'class="' . $params['class'] . '" ';

        }

        $html .= 'id="' . $params['id'] . '" data-name="' . $params['name'] . '" data-placeholder="' . $params['placeholder'] . '" style="position: relative;">' . $params['html'] . '</div>';

        return $html;
    }

    /**
     * @return string
     */
    public function getInfoHtml()
    {
        return '<span id="zeroinfos"></span>';
    }
}