<?php

namespace Drupal\islandora_oai\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configuration form for the standard Islandora OAI request handler.
 */
class HandlerAdmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_oai_handler_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['islandora_oai.settings', 'islandora_oai.formats'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [
      '#tree' => TRUE,
    ];
    $form['islandora_oai_configuration'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => TRUE,
    ];
    $form['islandora_oai_configuration']['islandora_oai_date_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Solr Date Field'),
      '#size' => '50',
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_date_field'),
      '#description' => $this->t('The Solr field containing the date to be used.'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_collection_field'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Solr RELS-EXT Collection Field'),
      '#size' => '50',
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_collection_field'),
      '#description' => $this->t('The Solr fields used to determine what collection, if any, an object is a member of.'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_content_model_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Solr Content Model Field'),
      '#size' => '50',
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_content_model_field'),
      '#description' => $this->t("Field which RELS-EXT datastreams use to define an object's content model."),
    ];
    $form['islandora_oai_configuration']['islandora_oai_solr_remove_base_filters'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove base Solr filters'),
      '#description' => $this->t('This option removes your configured Solr base filters from these queries. If you want your filters to be applied even though they could affect which objects are returned in the OAI results, uncheck this option.'),
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_solr_remove_base_filters'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_exclude_content_models'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Excluded Content Models'),
      '#size' => '50',
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_exclude_content_models'),
      '#description' => $this->t('By default, all objects are visible to OAI metadata harvesters. This field allows you to exclude all objects with a certain content model, e.g "islandora:collectionCModel" to exclude all objects with the Islandora Core Collection content model. Content models are separated by line. NOTE: If islandora:collectionCModel is added, it will break the ListSets verb.'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude objects within the "islandora" namespace?'),
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_exclude_islandora_namespace'),
      '#description' => $this->t('If this option is selected, note that restrictions within the <a href="@solr_url">Islandora Solr Search</a> module must match up with those within the core <a href="@islandora_url">Islandora</a> module.', array(
        '@solr_url' => Url::fromRoute('islandora_solr.admin_settings')->toString(),
        '@islandora_url' => Url::fromRoute('islandora.admin_config')->toString(),
      )),
    ];

    $form['islandora_oai_configuration']['islandora_oai_append_dc_thumbnail'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Append on dc.identifier.thumbnail to OAI_DC requests?'),
      '#default_value' => \Drupal::config('islandora_oai.settings')->get('islandora_oai_append_dc_thumbnail'),
      '#description' => $this->t("If this option is selected, a link to an object's thumbnail will be added to OAI_DC responses."),
    ];
    if (!\Drupal::config('islandora.settings')->get('islandora_namespace_restriction_enforced')) {
      $form['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace']['#disabled'] = TRUE;
      $form['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace']['#description'] = $this->t('Excluding the Islandora namespace is only possible when namespace restrictions are enabled within the <a href="@islandora_url">Islandora</a> module.', [
        '@islandora_url' => Url::fromRoute('islandora.admin_config')->toString(),
      ]);
    }

    $metadata_format_options = [];
    $metadata_formats = [];
    $results = db_query('SELECT * FROM {islandora_oai_metadata_formats} ORDER BY name');
    foreach ($results as $row) {
      $metadata_format_options[$row->metadata_prefix] = \Drupal\Component\Utility\Unicode::strtoupper($row->metadata_prefix);
      $metadata_formats[$row->metadata_prefix] = $row;
    }

    $form['islandora_oai_metadata'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Metadata Format'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['islandora_oai_metadata']['islandora_oai_options'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Transformations'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#weight' => 123,
    ];
    $form['islandora_oai_metadata']['islandora_oai_metadata_format'] = [
      '#type' => 'select',
      '#name' => 'islandora_oai_metadata_format',
      '#title' => $this->t('Metadata Format'),
      '#options' => $metadata_format_options,
    ];

    $oai_invoke_files = \Drupal::moduleHandler()->invokeAll('islandora_oai_get_xsl_files');
    $transform_options = ['default' => $this->t('No transformation selected')];
    $transform_options = array_merge($transform_options, $oai_invoke_files);

    $formats_config = $this->config('islandora_oai.formats')->get('formats');
    foreach ($metadata_formats as $format_key => $format) {
      $format_config = $formats_config[$format_key];
      $default_transform = $format_config['islandora_oai_transform_file'];
      $default_self_transform = $format_config['islandora_oai_self_transform_file'];

      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_metadata_prefix'] = [
        '#type' => 'item',
        '#title' => $this->t('Metadata Prefix'),
        '#markup' => $format->metadata_prefix,
        '#description' => $this->t('Default metadata prefix for the selected format.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
      ];
      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_metadata_namespace'] = [
        '#type' => 'item',
        '#title' => $this->t('Metadata Namespace'),
        '#markup' => $format->metadata_namespace,
        '#description' => $this->t('Default metadata namespace for the selected format.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
      ];
      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_schema_location'] = [
        '#type' => 'item',
        '#title' => $this->t('Schema Location'),
        '#markup' => $format->oai2_schema,
        '#description' => $this->t("Default URI for the selected metadata format's schema."),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
      ];
      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_include_object_links'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Force include a link to the object within Islandora?'),
        '#description' => $this->t('This is used in cases where metadata may not have links or Handles that point back to the object in the repository. Services like WorldCat expect a linkback to the object. This functionality can be achieved using XSLTs as well.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
        '#default_value' => $format_config['islandora_oai_include_object_links'],
      ];
      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_object_links_xpath'] = [
        '#type' => 'textfield',
        '#title' => $this->t('XPath'),
        '#description' => $this->t('Optionally include an XPath to append the link under. Leave empty to append under the root element.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
            ":input[name='islandora_oai_metadata[{$format->metadata_prefix}][islandora_oai_include_object_links]']" => ['checked' => TRUE],
          ],
        ],
        '#default_value' => $format_config['islandora_oai_object_links_xpath'],
      ];
      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_object_links_field'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Field'),
        '#description' => $this->t('The name of the field to append the link into. Ex: For dc:identifier, just enter identifier.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
            ":input[name='islandora_oai_metadata[{$format->metadata_prefix}][islandora_oai_include_object_links]']" => ['checked' => TRUE],
          ],
        ],
        '#default_value' => $format_config['islandora_oai_object_links_field'],
      ];
      $form['islandora_oai_metadata'][$format->metadata_prefix]['islandora_oai_object_links_record_namespace'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Include record namespace?'),
        '#description' => $this->t('This will include the record namespace and prefix for the field listed above. If the default namespace is declared in your metadata avoid using this.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
            ":input[name='islandora_oai_metadata[{$format->metadata_prefix}][islandora_oai_include_object_links]']" => ['checked' => TRUE],
          ],
        ],
        '#default_value' => $format_config['islandora_oai_object_links_record_namespace'],
      ];
      $form['islandora_oai_metadata']['islandora_oai_options'][$format->metadata_prefix]['islandora_oai_metadata_datastream_id'] = [
        '#type' => 'textfield',
        '#size' => 30,
        '#title' => 'Metadata Datastream ID',
        '#default_value' => $format_config['islandora_oai_metadata_datastream_id'],
        '#description' => $this->t('(Note that this is case-sensitive)'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
      ];

      $form['islandora_oai_metadata']['islandora_oai_options'][$format->metadata_prefix]['islandora_oai_transform_file'] = [
        '#type' => 'select',
        '#title' => $this->t('File to use for transforming @metadata_prefix', ['@metadata_prefix' => $format->metadata_prefix]),
        '#options' => $transform_options,
        '#default_value' => $default_transform,
        '#description' => $this->t('XSL or XSLT file used to translate existing metadata to an appropriate OAI-PMH format.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
      ];

      $form['islandora_oai_metadata']['islandora_oai_options'][$format->metadata_prefix]['islandora_oai_self_transform_file'] = [
        '#type' => 'select',
        '#title' => $this->t('File to use for self transforming @metadata_prefix', ['@metadata_prefix' => $format->metadata_prefix]),
        '#options' => $transform_options,
        '#default_value' => $default_self_transform,
        '#description' => $this->t('XSL or XSLT file used to transform xml prior transforming to an appropriate OAI-PMH format.'),
        '#states' => [
          'visible' => [
            ':input[name="islandora_oai_metadata_format"]' => ['value' => $format->metadata_prefix],
          ],
        ],
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $metadata_formats = [];
    $results = db_query('SELECT metadata_prefix FROM {islandora_oai_metadata_formats}');
    foreach ($results as $row) {
      $metadata_formats[$row->metadata_prefix] = $row->metadata_prefix;
    }
    foreach ($metadata_formats as $format) {
      if ($form_state->getValues()['islandora_oai_metadata'][$format]['islandora_oai_include_object_links']) {
        $field = trim($form_state->getValues()['islandora_oai_metadata'][$format]['islandora_oai_object_links_field']);
        if (empty($field)) {
          $form_state->setError($form['islandora_oai_metadata'][$format]['islandora_oai_object_links_field'], $this->t('The field must not be empty.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('islandora_oai.settings');
    $config->set('islandora_oai_collection_field', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_collection_field'])->save();
    $config->set('islandora_oai_content_model_field', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_content_model_field'])->save();
    $config->set('islandora_oai_exclude_content_models', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_exclude_content_models'])->save();
    $config->set('islandora_oai_date_field', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_date_field'])->save();
    $config->set('islandora_oai_exclude_islandora_namespace', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_exclude_islandora_namespace'])->save();
    $config->set('islandora_oai_append_dc_thumbnail', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_append_dc_thumbnail'])->save();
    $config->set('islandora_oai_solr_remove_base_filters', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_solr_remove_base_filters'])->save();

    // Loop through our transform options.
    $formats_config = [];
    $formats_config_object = $this->config('islandora_oai.formats');
    foreach ($form_state->getValues()['islandora_oai_metadata']['islandora_oai_options'] as $key => $value) {
      $formats_config[$key] = $value;
    }
    // Loop through object linking.
    $metadata_formats = [];
    $results = db_query('SELECT metadata_prefix FROM {islandora_oai_metadata_formats}');
    foreach ($results as $row) {
      $metadata_formats[$row->metadata_prefix] = $row->metadata_prefix;
    }
    foreach ($metadata_formats as $format) {
      foreach ($form_state->getValues()['islandora_oai_metadata'][$format] as $key => $value) {
        $formats_config[$format][$key] = $value;
      }
    }
    $formats_config_object->set('formats', $formats_config);
    $formats_config_object->save();
    $config->save();
    drupal_set_message($this->t('The configuration options have been saved.'));
  }

}
