<?php
/**
 * @file
 * Houses Islandora OAI request handler specific configuration.
 */

/**
 * Configuration form for the standard Islandora OAI request handler.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 *
 * @return array
 *   A renderable array representing the handler configuration form.
 */
function islandora_oai_handler_configuration_form($form, &$form_state) {
  $form = [
    '#tree' => TRUE,
  ];
  $form['islandora_oai_configuration'] = [
    '#type' => 'fieldset',
    '#title' => t('Configuration'),
    '#collapsible' => FALSE,
    '#collapsed' => TRUE,
  ];
  $form['islandora_oai_configuration']['islandora_oai_date_field'] = [
    '#type' => 'textfield',
    '#title' => t('Solr Date Field'),
    '#size' => '50',
    '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_date_field'),
    '#description' => t('The Solr field containing the date to be used.'),
  ]);
  $form['islandora_oai_configuration']['islandora_oai_collection_field'] = [
    '#type' => 'textarea',
    '#title' => t('Solr RELS-EXT Collection Field'),
    '#size' => '50',
    '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_collection_field'),
    '#description' => t('The Solr fields used to determine what collection, if any, an object is a member of.'),
  ];
  $form['islandora_oai_configuration']['islandora_oai_content_model_field'] = [
    '#type' => 'textfield',
    '#title' => t('Solr Content Model Field'),
    '#size' => '50',
    '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_content_model_field'),
    '#description' => t("Field which RELS-EXT datastreams use to define an object's content model."),
  ];
  $form['islandora_oai_configuration']['islandora_oai_solr_remove_base_filters'] = [
    '#type' => 'checkbox',
    '#title' => t('Remove base Solr filters'),
    '#description' => t('This option removes your configured Solr base filters from these queries. If you want your filters to be applied even though they could affect which objects are returned in the OAI results, uncheck this option.'),
    '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_solr_remove_base_filters'),
  ];
  $form['islandora_oai_configuration']['islandora_oai_exclude_content_models'] = [
    '#type' => 'textarea',
    '#title' => t('Excluded Content Models'),
    '#size' => '50',
    '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_exclude_content_models'),
    '#description' => t('By default, all objects are visible to OAI metadata harvesters. This field allows you to exclude all objects with a certain content model, e.g "islandora:collectionCModel" to exclude all objects with the Islandora Core Collection content model. Content models are separated by line. NOTE: If islandora:collectionCModel is added, it will break the ListSets verb.'),
  ];
  // @FIXME
// url() expects a route name or an external URI.
// $form['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace'] = array(
//     '#type' => 'checkbox',
//     '#title' => t('Exclude objects within the "islandora" namespace?'),
//     '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_exclude_islandora_namespace'),
//     '#description' => t('If this option is selected, note that restrictions within the <a href="@solr_url">Islandora Solr Search</a> module must match up with those within the core <a href="@islandora_url">Islandora</a> module.', array(
//       '@solr_url' => url('admin/islandora/search/islandora_solr/settings'),
//       '@islandora_url' => url('admin/islandora/configure'),
//     )),
//   );

  $form['islandora_oai_configuration']['islandora_oai_append_dc_thumbnail'] = [
    '#type' => 'checkbox',
    '#title' => t('Append on dc.identifier.thumbnail to OAI_DC requests?'),
    '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_append_dc_thumbnail'),
    '#description' => t("If this option is selected, a link to an object's thumbnail will be added to OAI_DC responses."),
  ];
  // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
// if (!variable_get('islandora_namespace_restriction_enforced', FALSE)) {
//     $form['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace']['#disabled'] = TRUE;
//     $form['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace']['#description'] = t('Excluding the Islandora namespace is only possible when namespace restrictions are enabled within the <a href="@islandora_url">Islandora</a> module.', array(
//       '@islandora_url' => url('admin/islandora/configure'),
//     ));
//   }

  $metadata_format_options = [];
  $metadata_formats = [];
  $results = db_query('SELECT * FROM {islandora_oai_metadata_formats} ORDER BY name');
  foreach ($results as $row) {
    $metadata_format_options[$row->name] = \Drupal\Component\Utility\Unicode::strtoupper($row->name);
    $metadata_formats[$row->name] = $row;
  }

  $form['islandora_oai_metadata'] = [
    '#type' => 'fieldset',
    '#title' => t('Metadata Format'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  ];
  $form['islandora_oai_metadata']['islandora_oai_options'] = [
    '#type' => 'fieldset',
    '#title' => t('Transformations'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
    '#weight' => 123,
  ];
  $form['islandora_oai_metadata']['islandora_oai_metadata_format'] = [
    '#type' => 'select',
    '#name' => 'islandora_oai_metadata_format',
    '#title' => t('Metadata Format'),
    '#options' => $metadata_format_options,
  ];

  $oai_invoke_files = \Drupal::moduleHandler()->invokeAll('islandora_oai_get_xsl_files');
  $transform_options = ['default' => t('No transformation selected')];
  $transform_options = array_merge($transform_options, $oai_invoke_files);

  foreach ($metadata_formats as $format) {
    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $default_transform = variable_get("islandora_oai_transform_file_$format->name", 'default');

    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $default_self_transform = variable_get("islandora_oai_self_transform_file_$format->name", 'default');

    $form['islandora_oai_metadata'][$format->name]['islandora_oai_metadata_prefix'] = [
      '#type' => 'item',
      '#title' => t('Metadata Prefix'),
      '#markup' => $format->metadata_prefix,
      '#description' => t('Default metadata prefix for the selected format.'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->name],
        ],
      ],
    ];
    $form['islandora_oai_metadata'][$format->name]['islandora_oai_metadata_namespace'] = [
      '#type' => 'item',
      '#title' => t('Metadata Namespace'),
      '#markup' => $format->metadata_namespace,
      '#description' => t('Default metadata namespace for the selected format.'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->name],
        ],
      ],
    ];
    $form['islandora_oai_metadata'][$format->name]['islandora_oai_schema_location'] = [
      '#type' => 'item',
      '#title' => t('Schema Location'),
      '#markup' => $format->oai2_schema,
      '#description' => t("Default URI for the selected metadata format's schema."),
      '#states' => [
        'visible' => [
          ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->name],
        ],
      ],
    ];
    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $form['islandora_oai_metadata'][$format->metadata_prefix]["islandora_oai_include_object_links_for_$format->metadata_prefix"] = array(
//       '#type' => 'checkbox',
//       '#title' => t('Force include a link to the object within Islandora?'),
//       '#description' => t('This is used in cases where metadata may not have links or Handles that point back to the object in the repository. Services like WorldCat expect a linkback to the object. This functionality can be achieved using XSLTs as well.'),
//       '#states' => array(
//         'visible' => array(
//           ':input[name="islandora_oai_metadata_format"]' => array('value' => $format->name),
//         ),
//       ),
//       '#default_value' => variable_get("islandora_oai_include_object_links_for_$format->metadata_prefix", FALSE),
//     );

    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $form['islandora_oai_metadata'][$format->metadata_prefix]["islandora_oai_object_links_for_{$format->metadata_prefix}_xpath"] = array(
//       '#type' => 'textfield',
//       '#title' => t('XPath'),
//       '#description' => t('Optionally include an XPath to append the link under. Leave empty to append under the root element.'),
//       '#states' => array(
//         'visible' => array(
//           ':input[name="islandora_oai_metadata_format"]' => array('value' => $format->name),
//           ":input[name='islandora_oai_metadata[{$format->metadata_prefix}][islandora_oai_include_object_links_for_{$format->metadata_prefix}]']" => array('checked' => TRUE),
//         ),
//       ),
//       '#default_value' => variable_get("islandora_oai_object_links_for_{$format->metadata_prefix}_xpath", ''),
//     );

    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $form['islandora_oai_metadata'][$format->metadata_prefix]["islandora_oai_object_links_for_{$format->metadata_prefix}_field"] = array(
//       '#type' => 'textfield',
//       '#title' => t('Field'),
//       '#description' => t('The name of the field to append the link into. Ex: For dc:identifier, just enter identifier.'),
//       '#states' => array(
//         'visible' => array(
//           ':input[name="islandora_oai_metadata_format"]' => array('value' => $format->name),
//           ":input[name='islandora_oai_metadata[{$format->metadata_prefix}][islandora_oai_include_object_links_for_{$format->metadata_prefix}]']" => array('checked' => TRUE),
//         ),
//       ),
//       '#default_value' => variable_get("islandora_oai_object_links_for_{$format->metadata_prefix}_field", ''),
//     );

    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $form['islandora_oai_metadata'][$format->metadata_prefix]["islandora_oai_object_links_for_{$format->metadata_prefix}_record_namespace"] = array(
//       '#type' => 'checkbox',
//       '#title' => t('Include record namespace?'),
//       '#description' => t('This will include the record namespace and prefix for the field listed above. If the default namespace is declared in your metadata avoid using this.'),
//       '#states' => array(
//         'visible' => array(
//           ':input[name="islandora_oai_metadata_format"]' => array('value' => $format->name),
//           ":input[name='islandora_oai_metadata[{$format->metadata_prefix}][islandora_oai_include_object_links_for_{$format->metadata_prefix}]']" => array('checked' => TRUE),
//         ),
//       ),
//       '#default_value' => variable_get("islandora_oai_object_links_for_{$format->metadata_prefix}_record_namespace", FALSE),
//     );

    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// $form['islandora_oai_metadata']['islandora_oai_options']["islandora_oai_metadata_datastream_id_$format->metadata_prefix"] = array(
//       '#type' => 'textfield',
//       '#size' => 30,
//       '#title' => 'Metadata Datastream ID',
//       '#default_value' => variable_get("islandora_oai_metadata_datastream_id_$format->metadata_prefix", 'DC'),
//       '#description' => t('(Note that this is case-sensitive)'),
//       '#states' => array(
//         'visible' => array(
//           ':input[name="islandora_oai_metadata_format"]' => array('value' => $format->name),
//         ),
//       ),
//     );

    $form['islandora_oai_metadata']['islandora_oai_options']["islandora_oai_transform_file_$format->metadata_prefix"] = [
      '#type' => 'select',
      '#title' => t('File to use for transforming @metadata_prefix', ['@metadata_prefix' => $format->metadata_prefix]),
      '#options' => $transform_options,
      '#default_value' => $default_transform,
      '#description' => t('XSL or XSLT file used to translate existing metadata to an appropriate OAI-PMH format.'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->name],
        ],
      ],
    ];

    $form['islandora_oai_metadata']['islandora_oai_options']["islandora_oai_self_transform_file_$format->metadata_prefix"] = [
      '#type' => 'select',
      '#title' => t('File to use for self transforming @metadata_prefix', ['@metadata_prefix' => $format->metadata_prefix]),
      '#options' => $transform_options,
      '#default_value' => $default_self_transform,
      '#description' => t('XSL or XSLT file used to transform xml prior transforming to an appropriate OAI-PMH format.'),
      '#states' => [
        'visible' => [
          ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->name],
        ],
      ],
    ];
  }
  $form['submit'] = [
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  ];
  return $form;
}

/**
 * Validation for the handler configuration form.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 */
function islandora_oai_handler_configuration_form_validate($form, &$form_state) {
  $metadata_formats = [];
  $results = db_query('SELECT metadata_prefix FROM {islandora_oai_metadata_formats}');
  foreach ($results as $row) {
    $metadata_formats[$row->metadata_prefix] = $row->metadata_prefix;
  }
  foreach ($metadata_formats as $format) {
    if ($form_state['values']['islandora_oai_metadata'][$format]["islandora_oai_include_object_links_for_{$format}"]) {
      $field = trim($form_state['values']['islandora_oai_metadata'][$format]["islandora_oai_object_links_for_{$format}_field"]);
      if (empty($field)) {
        form_error($form['islandora_oai_metadata'][$format]["islandora_oai_object_links_for_{$format}_field"], t('The field must not be empty.'));
      }
    }
  }
}

/**
 * Submit handler for the handler configuration form.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 */
function islandora_oai_handler_configuration_form_submit($form, &$form_state) {
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_collection_field', $form_state['values']['islandora_oai_configuration']['islandora_oai_collection_field'])->save();
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_content_model_field', $form_state['values']['islandora_oai_configuration']['islandora_oai_content_model_field'])->save();
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_exclude_content_models', $form_state['values']['islandora_oai_configuration']['islandora_oai_exclude_content_models'])->save();
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_date_field', $form_state['values']['islandora_oai_configuration']['islandora_oai_date_field'])->save();
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_exclude_islandora_namespace', $form_state['values']['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace'])->save();
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_append_dc_thumbnail', $form_state['values']['islandora_oai_configuration']['islandora_oai_append_dc_thumbnail'])->save();
  \Drupal::configFactory()->getEditable('islandora_oai.settings')->set('islandora_oai_solr_remove_base_filters', $form_state['values']['islandora_oai_configuration']['islandora_oai_solr_remove_base_filters'])->save();
  // Loop through our transform options.
  foreach ($form_state['values']['islandora_oai_metadata']['islandora_oai_options'] as $key => $value) {
    // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// variable_set($key, $value);

  }
  // Loop through object linking.
  $metadata_formats = [];
  $results = db_query('SELECT metadata_prefix FROM {islandora_oai_metadata_formats}');
  foreach ($results as $row) {
    $metadata_formats[$row->metadata_prefix] = $row->metadata_prefix;
  }
  foreach ($metadata_formats as $format) {
    foreach ($form_state['values']['islandora_oai_metadata'][$format] as $key => $value) {
      // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
// variable_set($key, trim($value));

    }
  }
  drupal_set_message(t('The configuration options have been saved.'));
}

/**
 * Renders a file management form.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 *
 * @return array
 *   An array representing the file management form.
 */
function islandora_oai_file_management_form($form, &$form_state) {
  // Grab all the user uploaded files.
  $oai_uploaded_files = [];
  $upload_path = 'public://islandora_oai_xsls';
  $uploaded_files = file_scan_directory($upload_path, '/.*\.xslt?$/');

  foreach ($uploaded_files as $up_file) {
    // @FIXME
// l() expects a Url object, created from a route name or external URI.
// $oai_uploaded_files[$up_file->uri] = array(
//       $up_file->filename,
//       l(t('download'), file_create_url($up_file->uri)),
//     );

  }
  ksort($oai_uploaded_files);
  $form['islandora_oai_files'] = [
    '#type' => 'fieldset',
    '#title' => t('Files'),
    '#collapsed' => FALSE,
    '#collapsible' => FALSE,
  ];
  $form['islandora_oai_files']['table'] = [
    '#type' => 'tableselect',
    '#header' => [
      t('File name'),
      t('Operations'),
    ],
    '#options' => $oai_uploaded_files,
    '#empty' => t('No uploaded files!'),
  ];
  if (count($oai_uploaded_files)) {
    $form['islandora_oai_files']['remove_selected'] = [
      '#type' => 'submit',
      '#value' => t('Delete selected'),
    ];
  }
  $form['islandora_oai_files']['islandora_oai_upload'] = [
    '#type' => 'fieldset',
    '#title' => t('Upload'),
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
  ];

  $form['islandora_oai_files']['islandora_oai_upload']['islandora_oai_upload_xsl'] = [
    '#type' => 'file',
    '#title' => t('Upload an XSL or XSLT file to be used for transformations'),
    '#upload_location' => 'public://islandora_oai_xsls',
    '#upload_validators' => [
      'file_validate_extensions' => ['xsl xslt'],
    ],
  ];
  $form['islandora_oai_files']['islandora_oai_upload']['islandora_oai_upload_button'] = [
    '#type' => 'submit',
    '#value' => t('Upload'),
    '#submit' => ['islandora_oai_upload_file'],
  ];
  return $form;
}

/**
 * Validation for the islandora_oai_file_management form.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 */
function islandora_oai_file_management_form_validate($form, $form_state) {
  if (end($form_state['triggering_element']['#parents']) == 'remove_selected') {
    $selected = FALSE;
    foreach ($form_state['values']['table'] as $value) {
      if ($value !== 0) {
        $selected = TRUE;
        break;
      }
    }
    if (!$selected) {
      form_set_error('table', t('Must select at least one entry to delete!'));
    }
  }
}
/**
 * Submit handler for the islandora_oai_file_management form.
 *
 * @param array $form
 *   An array representing a Drupal form.
 * @param array $form_state
 *   An array containing the Drupal form state.
 */
function islandora_oai_file_management_form_submit($form, &$form_state) {
  $deleted_count = 0;
  $fid_or = db_or();
  foreach ($form_state['values']['table'] as $uri => $selected) {
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
    file_delete(file_load($fid['fid']));
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
