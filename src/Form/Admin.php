<?php

namespace Drupal\islandora_oai\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Module administration form.
 */
class Admin extends ConfigFormBase {

  protected $moduleHandler;
  protected $routerBuilder;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, RouteBuilderInterface $router_builder) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'islandora_oai_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['islandora_oai.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $config = $this->config('islandora_oai.settings');
    $form = [
      '#tree' => TRUE,
    ];
    $form['islandora_oai_configuration'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration'),
      '#collapsible' => FALSE,
      '#collapsed' => TRUE,
    ];
    $form['islandora_oai_configuration']['islandora_oai_repository_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Repository Name'),
      '#required' => TRUE,
      '#size' => '50',
      '#default_value' => $config->get('islandora_oai_repository_name'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to the Repository'),
      '#field_prefix' => $base_url . '/',
      '#required' => TRUE,
      '#size' => '50',
      '#default_value' => $config->get('islandora_oai_path'),
      '#description' => $this->t('The path where the OAI-PMH service will respond, e.g. @base_url/oai2', ['@base_url' => $base_url]),
    ];
    $form['islandora_oai_configuration']['islandora_oai_repository_identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Repository unique identifier'),
      '#required' => TRUE,
      '#size' => '50',
      '#default_value' => $config->get('islandora_oai_repository_identifier'),
      '#description' => $this->t('The identifier for this repository, e.g. oai:<strong>drupal-site.org</strong>:123.'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_admin_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrator Email'),
      '#size' => '50',
      '#default_value' => $config->get('islandora_oai_admin_email'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_max_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum Response Size'),
      '#size' => '50',
      '#default_value' => $config->get('islandora_oai_max_size'),
      '#description' => $this->t('The maximum number of records to issue per response. If the result set is larger than this number, a resumption token will be issued'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_expire_time'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiration Time'),
      '#size' => '50',
      '#default_value' => $config->get('islandora_oai_expire_time'),
      '#description' => $this->t('The amount of time a resumption token will remain valid, in seconds. Defaults to one day (86400s).'),
    ];
    $form['islandora_oai_configuration']['islandora_oai_query_backend'] = [
      '#type' => 'radios',
      '#title' => $this->t('Query Backend'),
      '#default_value' => $config->get('islandora_oai_query_backend'),
      '#description' => $this->t('For larger repositories, OAI may perform poorly when attempting to perform the SPARQL queries it requires. In these cases, using the Solr backend may provide better results.'),
      '#options' => [
        'sparql' => $this->t('SPARQL'),
        'solr' => $this->t('Solr'),
      ],
    ];
    $solr_config_states = [
      'visible' => [
        ':input[name="islandora_oai_configuration[islandora_oai_query_backend]"]' => ['value' => 'solr'],
      ],
    ];
    $form['islandora_oai_configuration']['islandora_oai_solr_state_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Solr Object State Field'),
      '#default_value' => $config->get('islandora_oai_solr_state_field'),
      '#description' => $this->t("The field in Solr that holds a Fedora object's state ('Active', 'Inactive', or 'Deleted')."),
      '#states' => $solr_config_states,
    ];
    $form['islandora_oai_configuration']['islandora_oai_solr_collection_description_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Solr Collection Description Field'),
      '#default_value' => $config->get('islandora_oai_solr_collection_description_field'),
      '#description' => $this->t('The field in Solr to use for collection descriptions.'),
      '#states' => $solr_config_states,
    ];
    $form['islandora_oai_configuration']['islandora_oai_solr_object_ancestors_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Solr Object Ancestors Field'),
      '#default_value' => $config->get('islandora_oai_solr_object_ancestors_field'),
      '#description' => $this->t("A multivalued string The field in Solr that defines an object's ancestors. If left blank, Solr will recurse manually to get the child tree of a particular set. Use of this field may return a different set of children than the recursive option; it is the responsibility of the implementer to ensure the ancestors field returns an appropriate hierarchy of parents."),
      '#states' => $solr_config_states,
    ];

    // Build up the available request handlers.
    $defined_handlers = $this->moduleHandler->invokeAll(ISLANDORA_OAI_REQUEST_HANDLER_HOOK);
    if (!empty($defined_handlers)) {
      $form['islandora_oai_configuration']['handlers'] = [
        '#type' => 'item',
        '#title' => $this->t('Select an OAI request handler'),
        '#description' => $this->t('Preferred OAI request handler for Islandora. These may be provided by third-party modules.'),
        '#tree' => TRUE,
        '#theme' => 'islandora_viewers_table',
      ];
      foreach ($defined_handlers as $name => $profile) {
        $options[$name] = '';
        $form['islandora_oai_configuration']['handlers']['name'][$name] = [
          '#type' => 'hidden',
          '#value' => $name,
        ];
        $form['islandora_oai_configuration']['handlers']['label'][$name] = [
          '#type' => 'item',
          '#markup' => $profile['label'],
        ];
        $form['islandora_oai_configuration']['handlers']['description'][$name] = [
          '#type' => 'item',
          '#markup' => $profile['description'],
        ];
        $form['islandora_oai_configuration']['handlers']['configuration'][$name] = [
          '#type' => 'item',
          '#markup' => (isset($profile['configuration']) and $profile['configuration'] != '') ? Link::createFromRoute($this->t('configure'), $profile['configuration'])->toString() : '',
        ];

      }
      $form['islandora_oai_configuration']['handlers']['default'] = [
        '#type' => 'radios',
        '#options' => isset($options) ? $options : [],
        '#default_value' => $config->get('islandora_oai_request_handler'),
      ];
    }
    else {
      $form['islandora_oai_configuration']['handlers']['no_viewers'] = [
        '#markup' => $this->t('No viewers detected.'),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('islandora_oai.settings');
    $config->set('islandora_oai_repository_name', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_repository_name'])->save();
    $config->set('islandora_oai_path', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_path'])->save();
    $config->set('islandora_oai_repository_identifier', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_repository_identifier'])->save();
    $config->set('islandora_oai_admin_email', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_admin_email'])->save();
    $config->set('islandora_oai_max_size', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_max_size'])->save();
    $config->set('islandora_oai_expire_time', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_expire_time'])->save();
    $config->set('islandora_oai_request_handler', $form_state->getValues()['islandora_oai_configuration']['handlers']['default'])->save();
    $config->set('islandora_oai_query_backend', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_query_backend'])->save();
    $config->set('islandora_oai_solr_state_field', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_solr_state_field'])->save();
    $config->set('islandora_oai_solr_collection_description_field', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_solr_collection_description_field'])->save();
    $config->set('islandora_oai_solr_object_ancestors_field', $form_state->getValues()['islandora_oai_configuration']['islandora_oai_solr_object_ancestors_field'])->save();
    // Because of the dynamic pathing of the OAI path we need to rebuild the
    // menus.
    $this->routerBuilder->rebuild();
    drupal_set_message($this->t('The configuration options have been saved.'));

    $config->save();
  }

}
