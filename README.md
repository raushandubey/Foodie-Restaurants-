# Foodie

A modern food ordering and delivery web application built with Laravel.

---

## Features
- User authentication and registration
- Product and category management
- Shopping cart and checkout
- Order management for users and admins
- Payment integration
- Admin dashboard for managing products, categories, orders, and users

---

## Code Review
This project follows Laravel best practices, including:
- MVC architecture
- RESTful controllers
- Eloquent ORM for database interactions
- Secure authentication and authorization
- Well-structured migrations and seeders
- Clean and maintainable codebase

If you wish to contribute or review the code, please follow the guidelines below:
1. Fork the repository and create a new branch for your feature or fix.
2. Write clear, concise commit messages.
3. Ensure your code passes all tests and follows PSR-12 coding standards.
4. Submit a pull request with a detailed description of your changes.

---

## Getting Started

### Prerequisites
- PHP >= 8.0
- Composer
- MySQL or compatible database
- Node.js & npm (for frontend assets)

### Installation
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd foodie
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install frontend dependencies:
   ```bash
   npm install && npm run build
   ```
4. Copy the example environment file and set your configuration:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
5. Set up your database in `.env` and run migrations & seeders:
   ```bash
   php artisan migrate --seed
   ```
6. Start the development server:
   ```bash
   php artisan serve
   ```

---

## Deployment
This application was deployed by **Raushan Dubey**. For deployment instructions or support, please contact the maintainer.

---

## License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
