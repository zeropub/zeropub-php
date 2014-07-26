<?php

namespace ZeroPub\Model;

/**
 * Class Ad
 *
 * @package ZeroPub\Model
 */
class Ad
{
    private $code;
    private $ad;
    private $class = null;
    private $width;
    private $height;
    private $placeholder = null;

    /**
     * @param string $code Nom de la zone
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $ad Code HTML de la publicité
     */
    public function setAd($ad)
    {
        $this->ad = $ad;
    }

    /**
     * @param string $class Nom de la classe CSS éventuelle
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param int $width Largeur de la zone
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @param int $height Hauteur de la zone
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @param string $placeholder URL de l'image de remplacement
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getAd()
    {
        return $this->ad;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }
}