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
   */
  public function response() {
    module_load_include('inc', 'islandora_oai', 'includes/request');
    $output = islandora_oai_parse_request();
    $response = new Response();
    if ($output) {
      $response->setContent($output);
    }
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
