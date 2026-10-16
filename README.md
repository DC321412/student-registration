# Student Registration Management System (Drupal 11)

A Student Registration Management System built on Drupal 11.4 implementing core Drupal architecture, Form API, custom module development, taxonomy vocabularies, content types, Twig templating, preprocess functions, Views, contributed modules, permissions, and configuration management.

---

## 🎯 Features & Requirements Implemented

1. **Content Type**:
   - `Student` (`student`) content type with custom fields:
     - First Name (`field_first_name`, text)
     - Last Name (`field_last_name`, text)
     - Email (`field_student_email`, email)
     - Phone Number (`field_phone_number`, telephone)
     - Department (`field_department`, taxonomy entity reference)
     - Course (`field_course`, taxonomy entity reference)
     - Profile Photo (`field_profile_photo`, image with max 2MB, jpg/png/webp)

2. **Taxonomy**:
   - `Department` (`department`) vocabulary: Computer Science, Information Technology, Mechanical Engineering, Electrical Engineering, Business Administration.
   - `Course` (`course`) vocabulary: Web Development & Architecture, Data Structures & Algorithms, Cloud Computing & DevOps, Artificial Intelligence & Machine Learning, Financial Accounting & Analysis, Robotics & Embedded Systems.

3. **Custom Module (`student_registration`)**:
   - Located at `web/modules/custom/student_registration/`.
   - Dedicated route at `/student-registration` (and `/student/register`).
   - Main menu integration: links for "Student Registration" and "Students Directory".

4. **Drupal Form API & Robust Validation**:
   - Built with `\Drupal\student_registration\Form\StudentRegistrationForm`.
   - **Required field validation** for names, email, phone, department, and course.
   - **Name format validation** (letters, hyphens, and apostrophes only).
   - **Email format & uniqueness validation** (prevents duplicate student registrations).
   - **Phone validation** (enforces valid 10 to 15 digit contact numbers).
   - **File upload restrictions** (validates file extensions and restricts file size to 2 MB).
   - Dynamic taxonomy dropdown population.
   - Automatic permanent file usage attachment upon student creation.

5. **Hooks**:
   - `hook_theme()`: Registers `student_registration_form` and `node__student__full` templates.
   - `hook_form_alter()`:
     - Adds security and confidentiality notices to the public registration form.
     - Adds administrator guidance alerts and enhanced field descriptions to the administrative student node form (`node_student_form`, `node_student_edit_form`).
   - `hook_entity_insert()`: Logs a notice to `dblog` whenever a new student registers.

6. **Custom Twig Templates & Preprocess Functions**:
   - `student-registration-form.html.twig`: Clean multi-section card layout with personal info, contact info, academic details, and live photo preview.
   - `node--student--full.html.twig`: Hero header with student avatar/fallback initials, student ID pill (`STU-00001`), academic details card, contact details card, and action links.
   - `student_registration_preprocess_node()`: Prepares custom variables (`student_full_name`, `student_id_code`, `formatted_phone`, `department_badge_class`, `registration_date`, `registration_time`, `department_name`, `course_name`).

7. **Views (`student_directory`)**:
   - URL: `/students`
   - Exposed filters for **Department** and **Course** dropdowns.
   - Exposed sorting by **Registration Date** (DESC/ASC) and **Student Name** (ASC/DESC).
   - Full pagination (10 items per page).
   - Table display with student thumbnail, name, email, phone, department, course, registration date, and administrative operations.

8. **Contributed Modules Integrated**:
   - `Admin Toolbar` & `Admin Toolbar Extra Tools`: Enhanced administrative menu and quick links.
   - `Pathauto` & `Token`: Automatic SEO-friendly URLs (`/students/[node:field_first_name]-[node:field_last_name]`).
   - `CTools`: Context and plugin conditions for Pathauto.

9. **Roles & Permissions**:
   - `student_administrator`: Administrative control over student records and directory.
   - `student`: Standard role for registered students.
   - `anonymous`: Granted permission to register (`register as student`) and view directory.

10. **Configuration Management**:
    - Synchronized with `../config/sync`.
    - Exported and imported cleanly via `drush cex` and `drush cim`.

11. **CSS & JavaScript Library**:
    - `student_registration/general` library loaded on registration form and student pages.
    - Responsive card layout, custom badges for departments, and input focus transitions.
    - Client-side live image preview using FileReader API.
    - Auto-formatting phone number mask as user types.

---

## 🚀 Key URLs & Routes

| Page | URL | Description |
|------|-----|-------------|
| **Home Portal** | `/` (or `/home`) | University portal home with live stats, features & recent students |
| **Student Registration** | `/student-registration` | Public student registration form |
| **Student Directory** | `/students` | Views listing with exposed filters, sorting & pager |
| **Student Profile** | `/students/[first-name]-[last-name]` | Pathauto URL for student detail view |
| **Admin Content** | `/admin/content` | Administrative student records & content management |

---

## 🛠️ Drush Commands Reference

```bash
# Clear Drupal cache
vendor/bin/drush cr

# Export active configuration to config/sync
vendor/bin/drush cex -y

# Import configuration from config/sync
vendor/bin/drush cim -y

# Check site and Drush status
vendor/bin/drush status
```

