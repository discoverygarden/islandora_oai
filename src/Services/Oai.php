<?php

namespace Drupal\islandora_oai\Services;

use Symfony\Component\HttpFoundation\Response;

/**
 * OAI service.
 */
class Oai {

  /**
   * Class constructor.
   */
  public function __construct() {
  }

  /**
   * Get OAI response.
   */
  public function oai() {
    module_load_include('inc', 'islandora_oai', 'includes/request');
    $output = islandora_oai_parse_request();
    $response = new Response();
    if ($output) {
      $response->setContent($output);
    }
    $response->headers->set('Content-Type', 'text/xml');
    return $response;
  }

}
