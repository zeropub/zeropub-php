<?php

namespace ZeroPub\Provider;

/**
 * Interface OutputInterface
 *
 * @package ZeroPub
 */
interface OutputInterface
{
    /**
     * @param array $params URLs des scripts
     *
     * @return mixed Code HTML pour les scripts
     */
    public function getScriptsHtml(array $params);

    /**
     * @param array $params Paramètres de la zone
     *
     * @return mixed Code HTML pour la zone ZeroPub
     */
    public function getAdHtml(array $params);

    /**
     * @return mixed Code HTML pour les infos ZeroPub
     */
    public function getInfoHtml();
}