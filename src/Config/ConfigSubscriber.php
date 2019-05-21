<?php

namespace Drupal\islandora_oai\Config;

use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber to rebuild the route when necessary.
 */
class ConfigSubscriber implements EventSubscriberInterface {

  const CONFIG = 'islandora_oai.settings';
  const CONFIG_KEY = 'islandora_oai_path';

  /**
   * The router builder to use.
   *
   * @var Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * Constructor.
   */
  public function __construct(RouteBuilderInterface $router_builder) {
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [ConfigEvents::SAVE => ['onConfigSave']];
  }

  /**
   * ConfigEvents::SAVE callback; rebuild the router if necessary.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    if ($config->getName() == static::CONFIG && $event->isChanged(static::CONFIG_KEY)) {
      $this->routerBuilder->rebuild();
    }
  }

}
