<?php

namespace BsbLocalizedTemplatePathStack\View\Resolver;

use SplFileInfo;
use Traversable;
use Zend\View\Exception;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Resolver\TemplatePathStack;

class LocalizedTemplatePathStack extends TemplatePathStack implements ResolverInterface
{

    /**
     * Which fallback locale should we use
     * @var string
     */
    protected $fallbackLocale;

    /**
     * @var string format of template name conversion
     */
    protected $nameConversionPattern = '#DIRNAME#/#FILENAME#-#LOCALE#.#EXTENSION#';

    /**
     * Configure object
     *
     * @param  array|Traversable $options
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expected array or Traversable object; received "%s"',
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'fallback_locale':
                    $this->setFallbackLocale($value);
                    break;
                case 'name_conversion_pattern':
                    $this->setNameConversionPattern($value);
                    break;
                default:
                    break;
            }
        }

        parent::setOptions($options);
    }

    /**
     * @param string $fallbackLocale
     */
    public function setFallbackLocale($fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * @param string $nameConversionPattern
     */
    public function setNameConversionPattern($nameConversionPattern)
    {
        $this->nameConversionPattern = $nameConversionPattern;
    }

    /**
     * @return string
     */
    public function getNameConversionPattern()
    {
        return $this->nameConversionPattern;
    }

    /**
     * Retrieve the filesystem path to a view script
     *
     * @param  string $name
     * @param  null|Renderer $renderer
     * @return string
     * @throws Exception\DomainException
     */
    public function resolve($name, Renderer $renderer = null)
    {
        $this->lastLookupFailure = false;

        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            throw new Exception\DomainException(
                'Requested scripts may not include parent directory traversal ("../", "..\\" notation)'
            );
        }

        if (!count($this->paths)) {
            $this->lastLookupFailure = static::FAILURE_NO_PATHS;
            return false;
        }

        $locale = \Locale::getDefault();

        // Ensure we have the expected file extension
        $defaultSuffix = $this->getDefaultSuffix();
        if (pathinfo($name, PATHINFO_EXTENSION) == '') {
            $name .= '.' . $defaultSuffix;
        }

        $names = array();

        // PATHINFO_DIRNAME/FILENAME-LOCALE.EXT
        $names[] = str_replace(
            array('#DIRNAME#', '#FILENAME#', '#LOCALE#', '#EXTENSION#'),
            array(pathinfo($name, PATHINFO_DIRNAME), pathinfo($name, PATHINFO_FILENAME), $locale, pathinfo($name, PATHINFO_EXTENSION)),
            $this->getNameConversionPattern());

        if ($this->getFallbackLocale()) {
            $names[] = str_replace(
                array('#DIRNAME#', '#FILENAME#', '#LOCALE#', '#EXTENSION#'),
                array(pathinfo($name, PATHINFO_DIRNAME), pathinfo($name, PATHINFO_FILENAME), $this->getFallbackLocale(), pathinfo($name, PATHINFO_EXTENSION)),
                $this->getNameConversionPattern());
        }

        foreach ($this->paths as $path) {
            foreach($names as $name) {
                $file = new SplFileInfo($path . $name);

                if ($file->isReadable()) {
                    // Found! Return it.
                    if (($filePath = $file->getRealPath()) === false && substr($path, 0, 7) === 'phar://') {
                        // Do not try to expand phar paths (realpath + phars == fail)
                        $filePath = $path . $name;
                        if (!file_exists($filePath)) {
                            break;
                        }
                    }
                    if ($this->useStreamWrapper()) {
                        // If using a stream wrapper, prepend the spec to the path
                        $filePath = 'zend.view://' . $filePath;
                    }
                    return $filePath;
                }
            }
        }

        $this->lastLookupFailure = static::FAILURE_NOT_FOUND;
        return false;
    }

}
