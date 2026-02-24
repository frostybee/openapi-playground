# OpenAPI Playground

A self-hosted OpenAPI documentation viewer with Swagger UI and RapiDoc support. Ideal for students and developers learning API documentation tools and for testing localhost APIs without CORS issues.

## Features

- Can be deployed to any web server environment.
- Provides both Swagger UI and RapiDoc documentation viewers.
- Enables seamless testing of localhost APIs without cross-origin restrictions.
- Designed to assist students and developers in learning API documentation tools.
- Lightweight implementation that doesn't depend on complex framework requirements.

## Requirements

- PHP 8.2 or greater.
- Web server (Apache, Nginx, or equivalent HTTP server).
- Modern web browser.
- No additional database or server-side dependencies required.

## Installation

### Option 1: Using Composer (Recommended)

Requires [Composer](https://getcomposer.org) to be installed.

1. Navigate to your web server's document root folder.
2. Run the following command:

   ```bash
   composer create-project frostybee/openapi-playground [project-name]
   ```

3. Point your web server's document root at the `public/` directory of the created folder.
4. Open the home page at `http://localhost/[project-name]`.

### Option 2: Manual Installation

1. Clone the repository or [download the ZIP](https://github.com/frostybee/openapi-playground/archive/refs/heads/main.zip) and extract it:

   ```bash
   git clone https://github.com/frostybee/openapi-playground.git [project-name]
   ```

2. Point your web server's document root at the `public/` directory of the cloned folder.
3. Open the home page at `http://localhost/[project-name]`.

## Resources

- [Swagger Petstore](https://github.com/swagger-api/swagger-petstore)

## License

This project is distributed under the [MIT License](LICENSE). Users are free to use, modify, and distribute the software in accordance with the license terms. Contributions and feedback are welcome.
