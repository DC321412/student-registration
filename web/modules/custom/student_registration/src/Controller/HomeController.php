<?php

namespace Drupal\student_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the Student Registration System homepage.
 */
class HomeController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a new HomeController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    DateFormatterInterface $date_formatter
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter')
    );
  }

  /**
   * Builds the homepage content.
   *
   * @return array
   *   A render array representing the homepage.
   */
  public function build() {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');

    // Live Statistics.
    $total_students = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'student')
      ->condition('status', 1)
      ->count()
      ->execute();

    $total_departments = $term_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', 'department')
      ->count()
      ->execute();

    $total_courses = $term_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', 'course')
      ->count()
      ->execute();

    // Fetch the 4 most recent registered students.
    $recent_nids = $node_storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'student')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 4)
      ->execute();

    $recent_students = [];
    if (!empty($recent_nids)) {
      /** @var \Drupal\node\NodeInterface[] $nodes */
      $nodes = $node_storage->loadMultiple($recent_nids);
      foreach ($nodes as $node) {
        $first_name = $node->get('field_first_name')->value ?? '';
        $last_name = $node->get('field_last_name')->value ?? '';
        $full_name = trim($first_name . ' ' . $last_name) ?: $node->getTitle();

        $dep_term = $node->get('field_department')->entity;
        $course_term = $node->get('field_course')->entity;
        $dep_name = $dep_term ? $dep_term->label() : $this->t('General');
        $course_name = $course_term ? $course_term->label() : $this->t('Not Assigned');

        $dep_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', (string) $dep_name));

        // Photo URL or fallback.
        $photo_url = '';
        if (!$node->get('field_profile_photo')->isEmpty()) {
          $file = $node->get('field_profile_photo')->entity;
          if ($file) {
            $photo_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
          }
        }

        $recent_students[] = [
          'id' => $node->id(),
          'full_name' => $full_name,
          'initials' => strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1)),
          'department' => $dep_name,
          'department_badge_class' => 'dept-badge-' . ($dep_slug ?: 'general'),
          'course' => $course_name,
          'date' => $this->dateFormatter->format($node->getCreatedTime(), 'custom', 'M j, Y'),
          'url' => $node->toUrl()->toString(),
        ];
      }
    }

    // Load academic departments with student counts.
    $departments = [];
    $dep_terms = $term_storage->loadByProperties(['vid' => 'department']);
    foreach ($dep_terms as $term) {
      $count = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'student')
        ->condition('status', 1)
        ->condition('field_department', $term->id())
        ->count()
        ->execute();

      $departments[] = [
        'id' => $term->id(),
        'name' => $term->label(),
        'description' => $term->getDescription(),
        'student_count' => $count,
        'url' => Url::fromRoute('view.student_directory.page_1', [], ['query' => ['department' => $term->id()]])->toString(),
      ];
    }

    return [
      '#theme' => 'student_home_page',
      '#stats' => [
        'students' => $total_students,
        'departments' => $total_departments,
        'courses' => $total_courses,
      ],
      '#recent_students' => $recent_students,
      '#departments' => $departments,
      '#attached' => [
        'library' => [
          'student_registration/general',
        ],
      ],
      '#cache' => [
        'tags' => ['node_list:student', 'taxonomy_term_list:department', 'taxonomy_term_list:course'],
      ],
    ];
  }

}

