<?php

namespace Drupal\islandora_oai\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * OAI controller.
 */
class Oai {

  /**
   * Get OAI response.
   *
   * XXX: We cannot use Drupal\Core\Cache\CacheableResponse here unless we
   *   change the manner in which the OAI response is output... presently, it
   *   dumps directly to "php://output".
   */
  public function response() {
    module_load_include('inc', 'islandora_oai', 'includes/request');

    // XXX: This directly outputs to "php://output", so no need to set the
    // response content... headers working due to output buffering (the whole
    // "can't send headers after content" would otherwise rear its head).
    islandora_oai_parse_request();
    $response = new Response();
    $response->headers->set('Content-Type', 'text/xml');
    return $response;
  }

  /**
   * Dynamic routing for OAI endpoint.
   */
  public static function routes() {
    $items = [];

    $items['islandora_oai_oai'] = new Route(
      \Drupal::config('islandora_oai.settings')->get('islandora_oai_path'),
      [
        '_title' => 'OAI2',
        '_controller' => static::class . '::response',
      ],
      ['_permission' => 'access content'],
      ['no_cache' => TRUE]
    );

    return $items;
  }

}
