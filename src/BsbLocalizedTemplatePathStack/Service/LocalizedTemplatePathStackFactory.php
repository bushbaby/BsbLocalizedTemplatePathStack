<?php

namespace BsbLocalizedTemplatePathStack\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use BsbLocalizedTemplatePathStack\View\Resolver AS ViewResolver;

class LocalizedTemplatePathStackFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return PathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config            = $serviceLocator->get('config');
        $templatePathStack = new ViewResolver\LocalizedTemplatePathStack();

        if (is_array($config) && isset($config['view_manager'])) {
            $config = $config['view_manager'];
            if (is_array($config)) {
                if (isset($config['template_path_stack'])) {
                    $templatePathStack->addPaths($config['template_path_stack']);
                }
                if (isset($config['default_template_suffix'])) {
                    $templatePathStack->setDefaultSuffix($config['default_template_suffix']);
                }
            }
        }

        $config = $serviceLocator->get('config');

        if (is_array($config) && isset($config['bsb_localized_template_path_stack'])) {
            $config = $config['bsb_localized_template_path_stack'];
            if (is_array($config)) {
                if (isset($config['fallback_locale']) && is_string($config['fallback_locale'])) {
                    $templatePathStack->setFallbackLocale($config['fallback_locale']);
                } else {
                    $templatePathStack->setFallbackLocale($serviceLocator->get('MvcTranslator')->getFallbackLocale());
                }
                if (isset($config['name_conversion_pattern'])) {
                    $templatePathStack->setNameConversionPattern($config['name_conversion_pattern']);
                }
            }
        }

        return $templatePathStack;
    }
}
