<?php

namespace Drupal\student_registration\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a student registration form using Drupal's Form API.
 */
class StudentRegistrationForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The file usage service.
   *
   * @var \Drupal\Core\File\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * Constructs a new StudentRegistrationForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   * @param \Drupal\Core\File\FileUsage\FileUsageInterface $file_usage
   *   The file usage service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EmailValidatorInterface $email_validator,
    FileUsageInterface $file_usage
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->emailValidator = $email_validator;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('email.validator'),
      $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#theme'] = 'student_registration_form';
    $form['#attached']['library'][] = 'student_registration/general';

    $form['intro'] = [
      '#type' => 'item',
      '#markup' => '<div class="student-form-intro">'
        . '<h2>' . $this->t('Student Registration Form') . '</h2>'
        . '<p>' . $this->t('Please complete the form below to register for the upcoming academic session. All fields marked with * are required.') . '</p>'
        . '</div>',
    ];

    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#placeholder' => $this->t('e.g., John'),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#attributes' => [
        'class' => ['student-input'],
        'autocomplete' => 'given-name',
      ],
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#placeholder' => $this->t('e.g., Doe'),
      '#required' => TRUE,
      '#maxlength' => 100,
      '#attributes' => [
        'class' => ['student-input'],
        'autocomplete' => 'family-name',
      ],
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#placeholder' => $this->t('e.g., john.doe@university.edu'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['student-input'],
        'autocomplete' => 'email',
      ],
      '#description' => $this->t('A valid email address is required. You will receive updates here.'),
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone Number'),
      '#placeholder' => $this->t('e.g., +1 555-234-5678 or 10-digit number'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['student-input'],
        'autocomplete' => 'tel',
      ],
      '#description' => $this->t('Provide a 10 to 15 digit contact number.'),
    ];

    // Load Department taxonomy terms.
    $department_options = $this->getTaxonomyOptions('department');
    $form['department'] = [
      '#type' => 'select',
      '#title' => $this->t('Department'),
      '#options' => $department_options,
      '#empty_option' => $this->t('- Select Department -'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['student-select'],
      ],
      '#description' => $this->t('Select your academic department.'),
    ];

    // Load Course taxonomy terms.
    $course_options = $this->getTaxonomyOptions('course');
    $form['course'] = [
      '#type' => 'select',
      '#title' => $this->t('Course'),
      '#options' => $course_options,
      '#empty_option' => $this->t('- Select Course -'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['student-select'],
      ],
      '#description' => $this->t('Select the primary course you are enrolling in.'),
    ];

    // Profile Photo upload with restrictions.
    $form['photo'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Profile Photo'),
      '#description' => $this->t('Allowed formats: PNG, JPG, JPEG, WEBP. Maximum file size: 2 MB.'),
      '#upload_location' => 'public://students/photos/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg webp'],
        'file_validate_size' => [2 * 1024 * 1024],
        'FileExtension' => ['extensions' => 'png jpg jpeg webp'],
        'FileSizeLimit' => ['fileLimit' => 2097152],
      ],
      '#required' => FALSE,
      '#attributes' => [
        'class' => ['student-photo-upload'],
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit Registration'),
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => ['btn-student-submit'],
      ],
    ];

    $form['actions']['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Clear / View Directory'),
      '#url' => \Drupal\Core\Url::fromRoute('student_registration.register'),
      '#attributes' => [
        'class' => ['btn-student-secondary'],
      ],
    ];

    return $form;
  }

  /**
   * Helper method to load options from taxonomy vocabulary.
   *
   * @param string $vid
   *   The taxonomy vocabulary ID.
   *
   * @return array
   *   Key-value array of term IDs and term names.
   */
  protected function getTaxonomyOptions($vid) {
    $options = [];
    try {
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
        'vid' => $vid,
      ]);
      foreach ($terms as $term) {
        $options[$term->id()] = $term->label();
      }
      natcasesort($options);
    }
    catch (\Exception $e) {
      \Drupal::logger('student_registration')->error('Error loading terms for @vid: @msg', [
        '@vid' => $vid,
        '@msg' => $e->getMessage(),
      ]);
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $first_name = trim((string) $form_state->getValue('first_name'));
    $last_name = trim((string) $form_state->getValue('last_name'));
    $email = trim((string) $form_state->getValue('email'));
    $phone = trim((string) $form_state->getValue('phone'));
    $department = $form_state->getValue('department');
    $course = $form_state->getValue('course');

    // First Name Validation.
    if (strlen($first_name) < 2) {
      $form_state->setErrorByName('first_name', $this->t('First name must be at least 2 characters long.'));
    }
    elseif (!preg_match("/^[a-zA-Z\s'-]+$/u", $first_name)) {
      $form_state->setErrorByName('first_name', $this->t('First name may only contain letters, spaces, hyphens, and apostrophes.'));
    }

    // Last Name Validation.
    if (strlen($last_name) < 2) {
      $form_state->setErrorByName('last_name', $this->t('Last name must be at least 2 characters long.'));
    }
    elseif (!preg_match("/^[a-zA-Z\s'-]+$/u", $last_name)) {
      $form_state->setErrorByName('last_name', $this->t('Last name may only contain letters, spaces, hyphens, and apostrophes.'));
    }

    // Email Validation.
    if (!$this->emailValidator->isValid($email)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address (e.g. user@domain.com).'));
    }
    else {
      // Check for duplicate student email.
      $existing_students = $this->entityTypeManager->getStorage('node')->loadByProperties([
        'type' => 'student',
        'field_student_email' => $email,
      ]);
      if (!empty($existing_students)) {
        $form_state->setErrorByName('email', $this->t('A student is already registered with this email address (@email). Please use a different email or contact support.', ['@email' => $email]));
      }
    }

    // Phone Number Validation.
    $digits_only = preg_replace('/[^\d]/', '', $phone);
    if (strlen($digits_only) < 10 || strlen($digits_only) > 15) {
      $form_state->setErrorByName('phone', $this->t('Please enter a valid phone number containing 10 to 15 digits.'));
    }

    // Department Validation.
    if (empty($department) || !$this->entityTypeManager->getStorage('taxonomy_term')->load($department)) {
      $form_state->setErrorByName('department', $this->t('Please select a valid academic department.'));
    }

    // Course Validation.
    if (empty($course) || !$this->entityTypeManager->getStorage('taxonomy_term')->load($course)) {
      $form_state->setErrorByName('course', $this->t('Please select a valid course.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $first_name = trim((string) $form_state->getValue('first_name'));
    $last_name = trim((string) $form_state->getValue('last_name'));
    $email = trim((string) $form_state->getValue('email'));
    $phone = trim((string) $form_state->getValue('phone'));
    $department_id = $form_state->getValue('department');
    $course_id = $form_state->getValue('course');
    $photo_fids = $form_state->getValue('photo');

    $full_name = $first_name . ' ' . $last_name;

    try {
      // Create student node.
      $student_storage = $this->entityTypeManager->getStorage('node');
      /** @var \Drupal\node\NodeInterface $student */
      $student = $student_storage->create([
        'type' => 'student',
        'title' => $full_name,
        'field_first_name' => $first_name,
        'field_last_name' => $last_name,
        'field_student_email' => $email,
        'field_phone_number' => $phone,
        'field_department' => ['target_id' => $department_id],
        'field_course' => ['target_id' => $course_id],
        'status' => 1,
      ]);

      // Handle photo upload.
      if (!empty($photo_fids) && is_array($photo_fids)) {
        $fid = reset($photo_fids);
        if ($fid) {
          /** @var \Drupal\file\FileInterface $file */
          $file = $this->entityTypeManager->getStorage('file')->load($fid);
          if ($file) {
            $file->setPermanent();
            $file->save();
            $this->fileUsage->add($file, 'student_registration', 'node', $student->id() ?? 0);
            $student->set('field_profile_photo', [
              'target_id' => $file->id(),
              'alt' => $full_name . ' Profile Photo',
              'title' => $full_name,
            ]);
          }
        }
      }

      $student->save();

      // Update file usage with new node id if file was attached.
      if (!empty($fid) && isset($file)) {
        $this->fileUsage->delete($file, 'student_registration', 'node', 0);
        $this->fileUsage->add($file, 'student_registration', 'node', $student->id());
      }

      $this->messenger()->addStatus($this->t('Student registration completed successfully for <strong>@name</strong>! Welcome to the university.', [
        '@name' => $full_name,
      ]));

      // Redirect to the created student page.
      $form_state->setRedirect('entity.node.canonical', ['node' => $student->id()]);
    }
    catch (\Exception $e) {
      \Drupal::logger('student_registration')->error('Failed to create student record: @message', [
        '@message' => $e->getMessage(),
      ]);
      $this->messenger()->addError($this->t('An unexpected error occurred while saving your registration. Please try again or contact the administrator.'));
    }
  }

}
