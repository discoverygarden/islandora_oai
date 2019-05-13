<?php

namespace Drupal\islandora_oai\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Url;
use Drupal\Core\Link;
use PDO;

/**
 * Renders a file management form.
 */
class FileManagement extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_oai_file_management_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form_state->loadInclude('islandora_oai', 'inc', 'includes/handler.admin');
    // Grab all the user uploaded files.
    $oai_uploaded_files = [];
    $upload_path = 'public://islandora_oai_xsls';
    $uploaded_files = file_scan_directory($upload_path, '/.*\.xslt?$/');

    foreach ($uploaded_files as $up_file) {
      $oai_uploaded_files[$up_file->uri] = [
        $up_file->filename,
        Link::fromTextAndUrl($this->t('download'), Url::fromUri(file_create_url($up_file->uri))),
      ];
    }
    ksort($oai_uploaded_files);
    $form['islandora_oai_files'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Files'),
      '#collapsed' => FALSE,
      '#collapsible' => FALSE,
    ];
    $form['islandora_oai_files']['table'] = [
      '#type' => 'tableselect',
      '#header' => [
        $this->t('File name'),
        $this->t('Operations'),
      ],
      '#options' => $oai_uploaded_files,
      '#empty' => $this->t('No uploaded files!'),
    ];
    if (count($oai_uploaded_files)) {
      $form['islandora_oai_files']['remove_selected'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete selected'),
      ];
    }
    $form['islandora_oai_files']['islandora_oai_upload'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Upload'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['islandora_oai_files']['islandora_oai_upload']['islandora_oai_upload_xsl'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload an XSL or XSLT file to be used for transformations'),
      '#upload_location' => 'public://islandora_oai_xsls',
      '#upload_validators' => [
        'file_validate_extensions' => ['xsl xslt'],
      ],
    ];
    $form['islandora_oai_files']['islandora_oai_upload']['islandora_oai_upload_button'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload'),
      '#submit' => ['islandora_oai_upload_file'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $deleted_count = 0;
    $fid_or = new Condition('OR');
    foreach ($form_state->getValue('table') as $uri => $selected) {
      if ($selected !== 0) {
        $fid_or->condition('uri', $uri, '=');
      }
    }
    $fids = db_select('file_managed', 'f')
      ->fields('f', ['fid', 'uri'])
      ->condition($fid_or)
      ->execute()
      ->fetchAllAssoc('fid', PDO::FETCH_ASSOC);
    foreach ($fids as $fid) {
      file_delete($fid['fid']);
      $deleted_count++;
    }
    if ($deleted_count > 0) {
      drupal_set_message(\Drupal::translation()->formatPlural($deleted_count,
        'Successfully deleted 1 file!',
        'Successfully deleted @count files!'
      ));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (end($form_state->getTriggeringElement()['#parents']) == 'remove_selected') {
      $selected = FALSE;
      foreach ($form_state->getValue('table') as $value) {
        if ($value !== 0) {
          $selected = TRUE;
          break;
        }
      }
      if (!$selected) {
        $form_state->setErrorByName('table', $this->t('Must select at least one entry to delete!'));
      }
    }
  }

}
