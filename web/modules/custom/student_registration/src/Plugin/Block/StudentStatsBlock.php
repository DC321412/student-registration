<?php

namespace Drupal\student_registration\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Student Stats & System Telemetry' block.
 *
 * @Block(
 *   id = "student_stats_block",
 *   admin_label = @Translation("Student Stats & System Telemetry"),
 *   category = @Translation("Student Registration")
 * )
 */
#[Block(
  id: "student_stats_block",
  admin_label: new TranslatableMarkup("Student Stats & System Telemetry"),
  category: new TranslatableMarkup("Student Registration")
)]
class StudentStatsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new StudentStatsBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'headline' => 'Portal Telemetry & Metrics',
      'show_telemetry' => TRUE,
      'show_cta' => TRUE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['headline'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Block Headline'),
      '#default_value' => $config['headline'],
      '#description' => $this->t('Custom title displayed above the statistics card.'),
    ];

    $form['show_telemetry'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display Live Telemetry Pill'),
      '#default_value' => $config['show_telemetry'],
      '#description' => $this->t('Shows the real-time latency and online connection status indicator.'),
    ];

    $form['show_cta'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display Quick Registration Button'),
      '#default_value' => $config['show_cta'],
      '#description' => $this->t('Renders a direct button to the student registration form.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['headline'] = $form_state->getValue('headline');
    $this->configuration['show_telemetry'] = (bool) $form_state->getValue('show_telemetry');
    $this->configuration['show_cta'] = (bool) $form_state->getValue('show_cta');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // Query live total counts from entity storage.
    $total_students = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'student')
      ->condition('status', 1)
      ->count()
      ->execute();

    $total_departments = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', 'department')
      ->count()
      ->execute();

    $total_courses = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', 'course')
      ->count()
      ->execute();

    return [
      '#theme' => 'student_stats_block',
      '#headline' => $config['headline'],
      '#show_telemetry' => $config['show_telemetry'],
      '#show_cta' => $config['show_cta'],
      '#stats' => [
        'students' => $total_students,
        'departments' => $total_departments,
        'courses' => $total_courses,
      ],
      '#cache' => [
        'tags' => [
          'node_list:student',
          'taxonomy_term_list:department',
          'taxonomy_term_list:course',
        ],
      ],
    ];
  }

}

