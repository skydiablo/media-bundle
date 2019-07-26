<?php


namespace SkyDiablo\MediaBundle\Service\MediaRouter;

/**
 * @author SkyDiablo <skydiablo@gmx.net>
 * Class WrappedCfRszIoMediaRouter
 */
class WrappedCfRszIoMediaRouter extends RszIoMediaRouter
{

    const URL_TEMPLATE = '://okskydiablo.com{PATH}?{QUERY}&w={WIDTH}&h={HEIGHT}&mode=max&format={FORMAT}';

    /**
     * switch between HTTP & HTTPS
     * @var bool
     */
    private $useEncryption = true;

    /**
     * @return boolean
     */
    public function isUseEncryption(): bool
    {
        return $this->useEncryption;
    }

    /**
     * @param boolean $useEncryption
     */
    public function setUseEncryption(bool $useEncryption)
    {
        $this->useEncryption = $useEncryption;
    }

    /**
     * @return mixed
     */
    protected function getURLTemplate()
    {
        return ($this->useEncryption ? 'https' : 'http') . self::URL_TEMPLATE;
    }


}