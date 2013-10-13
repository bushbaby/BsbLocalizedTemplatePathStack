<?php

namespace BsbLocalizedTemplatePathStack;

use Zend\Mvc\MvcEvent;
use Zend\View\Resolver\AggregateResolver;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        /** @var $sm \Zend\ServiceManager\ServiceManager */
        $sm = $e->getApplication()->getServiceManager();

        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Application', MvcEvent::EVENT_RENDER, function(MvcEvent $e) use ($sm) {
            /** @var AggregateResolver $ar */
            $ar = $sm->get('ViewResolver');
            $ar->attach($sm->get('BsbLocalizedTemplatePathStack\LocalizedTemplatePathStack'));
        }, 100);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'BsbLocalizedTemplatePathStack\LocalizedTemplatePathStack' =>
                    'BsbLocalizedTemplatePathStack\Service\LocalizedTemplatePathStackFactory',
            ),
        );
    }
}
