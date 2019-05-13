<?php

/**
 * @file
 * Houses Islandora OAI request handler specific configuration helpers.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Submit handler for uploading a file.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   Drupal form state.
 */
function islandora_oai_upload_file(array $form, FormStateInterface $form_state) {
  $file = file_save_upload('islandora_oai_upload_xsl', ['file_validate_extensions' => ['xsl xslt']], 'public://islandora_oai_xsls');
  if ($file) {
    $file = reset($file);
    $file->setPermanent();
    $file->save();
    drupal_set_message(t("The file @filename was uploaded successfully.",
      [
        '@filename' => $file->getFilename(),
      ]
    ));
  }
}
