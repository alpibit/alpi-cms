# Alpi CMS

Alpi CMS is a lightweight content management system designed to provide an efficient, yet minimalistic platform for content creators. It's built using pure PHP, HTML, JS, and CSS, ensuring a clean and straightforward codebase.

## Project Stage

Please note that this project is in its very early stages. At this stage, the project should not be considered usable in any shape or form. As development progresses, more features and improvements will be added.

## Features

- **Lightweight Design:** Designed for speed and simplicity.
- **Pure Code:** No frameworks or bulky libraries, just plain PHP, HTML, JS, and CSS.
- **Modular Structure:** Organized file and folder structure for easy scaling and maintenance.

## Installation

1. Ensure you are running a server with PHP version 8.2 or higher and the latest MySQL version.
2. Copy the Alpi CMS files to the root directory of your PHP MySQL server.
3. Navigate to `/install.php` in your browser.
4. Fill in the database details (hostname, database name, user, password) and desired admin credentials.
5. Run the installation script by clicking on the "Install" button. This will create the necessary tables in the database, a sample post, and set up the initial admin user based on the credentials you provided.
6. After installation, for security reasons, please delete or rename the `install.php` file.
7. Access the admin dashboard by navigating to `public/admin/index.php` on your server.
8. Log in using the admin credentials you set during the installation process. Start creating, modifying, and deleting posts. Note: At this stage, user management functionalities (adding, removing, or editing users) are not available.

## Contributing

Any contributions, from code to suggestions, are welcome! Please open an issue or submit a pull request.

## License

This project is licensed under the MIT License. See the LICENSE file for more information.

## Future Plans

- User authentication and role management.
- Advanced post editing capabilities.
- Media management module.
- And more!

Stay tuned for more updates and features!