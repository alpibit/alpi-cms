# Alpi CMS

Alpi CMS is a lightweight content management system designed to provide an efficient, yet minimalistic platform for content creators. It's built using pure PHP, HTML, JS, and CSS, ensuring a clean and straightforward codebase.

## Project Status

Please note that this project is in active development. At this stage, the project should not be considered ready for production use. I'm currently working on debugging, improving the styling, and refining features to bring Alpi CMS to its earliest usable state.

## Features

- **Lightweight Design:** Designed for speed and simplicity.
- **Pure Code:** No frameworks or bulky libraries, just plain PHP, HTML, JS, and CSS.
- **Modular Structure:** Organized file and folder structure for easy scaling and maintenance.
- **Content Blocks:** Flexible content creation with various block types (text, image, video, gallery, etc.).
- **Category Management:** Organize your content with categories.
- **File Upload:** Easy media management with built-in file upload functionality.
- **Responsive Admin Interface:** Manage your content from any device.
- **Custom Settings:** Customize your CMS through an intuitive settings interface.

## Installation

1. Ensure you are running a server with PHP version 8.2 or higher and the latest MySQL version.
2. Clone this repository or download the Alpi CMS files to your server's root directory.
3. Navigate to `/install.php` in your browser.
4. Fill in the database details (hostname, database name, user, password) and desired admin credentials.
5. Run the installation script by clicking on the "Install" button. This will create the necessary tables in the database, set up sample content, and create the initial admin user.
6. After installation, for security reasons, please delete or rename the `install.php` file.
7. Access the admin dashboard by navigating to `/admin` on your server. The automatic router will redirect you to the correct page.
8. Log in using the admin credentials you set during the installation process.

## Contributing

Any contributions, from code to suggestions, are welcome! Alpi CMS is a great project for both new and experienced developers to participate in. It can be an excellent opportunity for your first open-source contribution. Please open an issue or submit a pull request if you'd like to help improve the CMS.

## License

This project is licensed under the MIT License. See the LICENSE file for more information.

## Future Plans

- Implement caching for better performance
- Enhance security features

Stay tuned for more updates and features!