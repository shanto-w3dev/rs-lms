# RS LMS — WordPress Learning Management System (Plugin)

RS LMS is a lightweight, extensible WordPress plugin that provides core LMS features: courses, lessons, quizzes, enrollments, and basic reporting. It is designed to be developer-friendly with clear hooks, shortcodes, and a REST API surface so you can customize and extend functionality as needed.

- Version: 1.0.0
- Requires: WordPress 5.8+
- Tested up to: 6.x
- License: GPL-2.0-or-later

## Table of Contents
- Description
- Features
- Requirements
- Installation
- Quick Start
- Shortcodes & Blocks
- REST API
- Actions & Filters
- Troubleshooting
- FAQ
- Changelog
- Contributing
- License

## Description
RS LMS adds course, lesson, and quiz management to WordPress. It focuses on:

- Easy course creation with nested lessons.
- Student enrollment and progress tracking.
- Quiz engine for basic assessments.
- Simple reporting and exportable completion data.
- Developer hooks and REST endpoints for integrations.

This plugin intentionally keeps the core lightweight and extensible so it can be integrated into themes, page builders, and custom workflows.

## Features
- Custom post types: Course, Lesson, Quiz
- Enrollment management (manual and self-enroll)
- Progress tracking per user and course
- Basic quiz question types (multiple choice, true/false)
- Shortcodes and Gutenberg blocks for front-end display
- REST API for headless or external integrations
- Action/filter hooks for customization
- CSV export of users/completions

## Requirements
- PHP 7.4+ (7.4 recommended)
- MySQL 5.7+ / MariaDB equivalent
- WordPress 5.8+
- Recommended: WP REST API enabled (default in modern WP)

## Installation
1. Upload the `rs-lms` plugin folder to `/wp-content/plugins/` or install via WP admin plugin installer when packaged.
2. Activate the plugin from the 'Plugins' screen in WordPress.
3. Navigate to "RS LMS" in the admin menu and follow the setup prompts (create default pages and sample course if prompted).

Manual file-based install:
- Place plugin files at `/wp-content/plugins/rs-lms/`
- Activate in Plugins → Installed Plugins

## Quick Start
1. Create a Course: WP Admin → RS LMS → Add New Course.
2. Add Lessons: While editing a course, add lesson posts and order them.
3. Create Quizzes: WP Admin → RS LMS → Add New Quiz and attach to lessons or courses.
4. Enroll Students: Use the Enrollments screen to add users to courses or allow front-end enrollment.
5. Display a course list on any page with the shortcode: [rs_lms_course_list]

## Shortcodes & Blocks
Shortcodes:
- [rs_lms_course_list per_page="10" order="asc"]
- [rs_lms_course id="123"] — display course overview and enroll button
- [rs_lms_lesson id="456"] — render lesson content and navigation

Gutenberg Blocks:
- Course List block
- Course Detail block
(Blocks appear under "RS LMS" in the block inserter)

Attributes:
- per_page: number
- order: asc|desc
- id: post ID

## REST API
RS LMS exposes a small REST API under the namespace: `rs-lms/v1`
Example endpoints:
- GET /wp-json/rs-lms/v1/courses — list courses
- GET /wp-json/rs-lms/v1/courses/<id> — course details
- POST /wp-json/rs-lms/v1/enroll — enroll a user (authenticated)
Authentication: standard WP cookie auth for logged-in users or application passwords / JWT if configured separately.

## Actions & Filters
Actions:
- rs_lms_course_created (int $course_id, WP_Post $course)
- rs_lms_course_updated (int $course_id, WP_Post $course)
- rs_lms_user_enrolled (int $user_id, int $course_id)
- rs_lms_quiz_submitted (int $submission_id, array $data)

Filters:
- rs_lms_course_query_args (array $args) — modify main course query args
- rs_lms_enrollment_capabilities (array $caps, WP_User $user, int $course_id)

Use these hooks to integrate SSO, custom notifications, or analytics.

## Troubleshooting
- If courses or lessons do not appear, flush permalinks: Settings → Permalinks → Save.
- Check capabilities: Ensure your user role has RS LMS capabilities in Users → All Users or via role manager plugins.
- REST API 401/403: Confirm authentication method and permissions for the endpoint.

## FAQ
Q: Can I export course completion data?
A: Yes — RS LMS provides a CSV export for course enrollments and completion under RS LMS → Reports.

Q: Is there a student dashboard?
A: The plugin includes a basic dashboard. For advanced dashboards, use the REST API or customize the templates.

Q: Can I restrict lessons to enrolled students?
A: Yes — lessons attached to courses respect enrollment checks by default.

## Changelog
- 1.0.0 — Initial release
    - Core post types, enrollment, quizzes, shortcodes, REST API, hooks.

## Contributing
Contributions are welcome. Suggested workflow:
1. Fork the repository.
2. Create a feature branch.
3. Add tests and documentation for new features.
4. Submit a pull request with a clear description.

Please follow WordPress coding standards and include unit/phpunit tests for covered logic where possible.

## Tests
- Unit tests use PHPUnit. Run with: composer test (if composer.json provided)
- Integration tests: Recommend spinning a test WP environment (e.g., WP-CLI scaffold or Docker) to validate REST endpoints and capabilities.

## Support
- For issues and feature requests, open an issue in the project repository (if available).
- Include WP version, PHP version, active theme, and a list of active plugins when reporting bugs.

## License
RS LMS is licensed under the GNU General Public License v2.0 or later (GPL-2.0-or-later). See LICENSE file for details.

## Credits
- Author: Riadujjaman Shanto
- Special thanks to plugin users and community testers

If you need a customized README with branding, screenshots, or additional sections (developer examples, sample data, or installation scripts), specify which parts to expand.
