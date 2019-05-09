<?php

/**
 * @file
 * Houses Islandora OAI request handler specific configuration helpers.
 */

/**
 * Submit handler for uploading a file.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 */
function islandora_oai_upload_file($form, $form_state) {
  $file = file_save_upload('islandora_oai_upload_xsl', ['file_validate_extensions' => ['xsl xslt']], 'public://islandora_oai_xsls');
  if ($file) {
    $file->status = FILE_STATUS_PERMANENT;
    file_save($file);
    drupal_set_message(t("The file @filename was uploaded successfully.",
      [
        '@filename' => $file->filename,
      ]
    ));
  }
}
