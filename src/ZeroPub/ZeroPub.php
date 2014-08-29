<?php

namespace ZeroPub;

use ZeroPub\Exception\ZeroPubException;
use ZeroPub\Model\Ad;
use ZeroPub\Provider\OutputInterface;

/**
 * Classe principale de ZeroPub.
 *
 * @package ZeroPub
 */
class ZeroPub
{
    private $version = '1.0';
    private $scriptVersion = 1;
    private $siteId;
    private $siteSecret;
    private $active = 0;
    private $hostname;
    private $scriptName;
    private $ads = array();
    private $allowPhp = false;

    /** @var OutputInterface */
    private $output = null;

    const ACTIVITY_NONE = 0;

    const ACTIVITY_ACTIVE = 1;

    const ACTIVITY_DEBUG = 2;

    /**
     * @param Ad $ad Zone ZeroPub
     *
     * @return string
     */
    public function registerAd(Ad $ad)
    {
        $code = $ad->getCode();

        $this->ads[$code] = $ad;

        return $code;
    }

    /**
     * @param string $code Nom de la zone
     *
     * @return Model\Ad
     * @throws ZeroPubException
     */
    private function findAdByCode($code)
    {
        if (!array_key_exists($code, $this->ads)) {
            throw new ZeroPubException(sprintf('Aucune zone ZeroPub trouvée avec le nom %s.', $code));
        }

        return $this->ads[$code];
    }

    /**
     * @param string $code
     *
     * @return null|string
     */
    public function displayAd($code)
    {
        $scriptName = $this->getScriptName();
        $ad = $this->findAdByCode($code);

        $adClass = $ad->getClass();
        $adAd = $ad->getAd();
        $adPlaceholder = $ad->getPlaceholder();

        $parts = explode('/', $scriptName);
        $placeholderName = (string) $parts[1];

        if (empty($placeholderName)) {
            $placeholderName = 'placeholder';
        }

        $hash = md5($code);

        if ($this->isPhpAllowed()) {

            preg_match_all('#(<\s*\?php)(.*?)(\?\s*>)#msi', $adAd, $r);

            foreach ($r[2] as $i => $php) {

                ob_start();

                eval($php);
                $data = ob_get_contents();

                ob_end_clean();

                $adAd = str_replace($r[0][$i], $data, $adAd);

            }

        }

        if (null !== $adPlaceholder) {

            $placeholder = 'http://' . $this->getHostname() . '/' . $scriptName . '/' . md5($code);

        } else {

            $placeholder = 'http://' . $this->getHostname() . '/' . $placeholderName . '/' . $ad->getWidth() . '/' . $ad->getHeight();

        }

        return $this->output->getAdHtml(array('id' => $hash, 'class' => $adClass, 'name' => $hash, 'placeholder' => $placeholder, 'html' => $adAd));
    }

    /**
     * Affiche les informations
     *
     * @return string
     */
    public function displayInfo()
    {
        return $this->output->getInfoHtml();
    }

    /**
     * @param int|null $forceLevel Force le niveau de déclenchement du script.
     *
     * @return string
     */
    public function displayScripts($forceLevel=null)
    {
        $hostname = $this->getHostname();
        $scriptName = $this->getScriptName();
        $scriptVersion = $this->getScriptVersion();

        $args = array();

        if ($scriptVersion != 1) {

            $args['ver'] = $scriptVersion;

        }

        if ($forceLevel !== null) {

            $args['level'] = $forceLevel;

        }

        $jsArgs = '?' . http_build_query($args);

        return $this->output->getScriptsHtml(array(
                'js' => array(
                    'honeypot' => 'http://' . $hostname . '/advertisement.js',
                    'main' => 'http://' . $hostname . '/' . $scriptName . '.js' . $jsArgs,
                ),
            )
        );
    }

    /**
     * @param int $siteId ID du site sur ZeroPub.
     *
     * @throws ZeroPubException
     */
    public function setSiteId($siteId)
    {
        if (!is_int($siteId)) {
            throw new ZeroPubException('La variable siteId doit être un entier.');
        }

        $this->siteId = $siteId;
    }

    /**
     * @param string $siteSecret Clé secrète pour l'API ZeroPub.
     *
     * @throws ZeroPubException
     */
    public function setSiteSecret($siteSecret)
    {

        if (strlen($siteSecret) !== 32) {
            throw new ZeroPubException('La clé secrète doit faire 32 caractères.');
        }

        $this->siteSecret = $siteSecret;
    }

    /**
     * @param int $active Indice d'activité du script sur ZeroPub.
     *
     * @throws ZeroPubException
     */
    public function setActive($active)
    {
        if (!is_int($active) || $active < 0 || $active > 2) {
            throw new ZeroPubException('La variable active doit être un entier entre 0 et 2.');
        }

        $this->active = $active;
    }

    /**
     * @param string $hostname Nom d'hôte ZeroPub.
     *
     * @throws ZeroPubException
     */
    public function setHostname($hostname)
    {
        if (!preg_match('/^\w+\.\w+\.\w+$/', $hostname)) {
            throw new ZeroPubException('La variable hostname doit contenir un nom d\'hôte.');
        }

        $this->hostname = $hostname;
    }

    /**
     * @param string $scriptName Nom du script fourni par ZeroPub.
     *
     * @throws ZeroPubException
     */
    public function setScriptName($scriptName)
    {
        if (false === strpos($scriptName, '/')) {
            throw new ZeroPubException('Le nom du script ne contient pas de slash (/).');
        }

        $this->scriptName = $scriptName;
    }

    /**
     * @param int $scriptVersion
     */
    public function setScriptVersion($scriptVersion)
    {
        $this->scriptVersion = (int) $scriptVersion;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getSiteSecret()
    {
        return $this->siteSecret;
    }

    /**
     * @return int
     */
    private function getScriptVersion()
    {
        return $this->scriptVersion;
    }

    /**
     * @return mixed
     */
    private function getScriptName()
    {
        return $this->scriptName;
    }

    /**
     * @return mixed
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return bool
     */
    public function isPhpAllowed()
    {
        return $this->allowPhp;
    }

    /**
     * Autoriser le PHP dans les zones
     */
    public function allowPhp()
    {
        $this->allowPhp = true;
    }

    /**
     * Ne pas autoriser le PHP dans les zones
     */
    public function disallowPhp()
    {
        $this->allowPhp = false;
    }
}