# ECR Wings Academy - Backend API

A Laravel-based REST API for managing ECR Wings Academy's educational platform.

## Features

- **Admin Authentication**: Secure login system with token-based authentication
- **Course Management**: CRUD operations for courses
- **Student Management**: Complete student information management
- **Enrollment System**: Track student enrollments and payments
- **MySQL Database**: Robust data storage with proper relationships
- **API Documentation**: RESTful endpoints with validation

## Tech Stack

- **Backend**: Laravel 12
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Validation**: Built-in Laravel validation
- **CORS**: Configured for frontend integration

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   Update your `.env` file with your MySQL credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ecr_wings_academy
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

   If you're deploying the frontend (e.g. https://ecrfrontend.onrender.com), also set:
   ```env
   FRONTEND_URL=https://ecrfrontend.onrender.com
   CORS_ALLOWED_ORIGINS=https://ecrfrontend.onrender.com
   SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:5173,ecrfrontend.onrender.com
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

## Default Admin Accounts

After running the seeders, you'll have these admin accounts:

- **Super Admin**:
  - Email: `admin@ecrwings.com`
  - Password: `admin123`

- **Test Admin**:
  - Email: `test@ecrwings.com`
  - Password: `test123`

## API Endpoints

### Authentication
- `POST /api/admins/login` - Admin login

### Admin Management
- `GET /api/admins` - List all admins
- `POST /api/admins` - Create new admin
- `GET /api/admins/{id}` - Get admin details
- `PUT /api/admins/{id}` - Update admin
- `DELETE /api/admins/{id}` - Delete admin

### Course Management
- `GET /api/courses` - List all courses
- `POST /api/courses` - Create new course
- `GET /api/courses/{id}` - Get course details
- `PUT /api/courses/{id}` - Update course
- `DELETE /api/courses/{id}` - Delete course

### Student Management
- `GET /api/students` - List all students
- `POST /api/students` - Create new student
- `GET /api/students/{id}` - Get student details
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student

### Enrollment Management
- `GET /api/enrollments` - List all enrollments
- `POST /api/enrollments` - Create new enrollment
- `GET /api/enrollments/{id}` - Get enrollment details
- `PUT /api/enrollments/{id}` - Update enrollment
- `DELETE /api/enrollments/{id}` - Delete enrollment
- `GET /api/enrollments/student/{student_id}` - Get enrollments by student
- `GET /api/enrollments/course/{course_id}` - Get enrollments by course

## Authentication

All endpoints except `/api/admins/login` require authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your_token}
```

## Database Schema

### Admins Table
- `id`, `name`, `email`, `password`, `role`, `is_active`, `timestamps`

### Courses Table
- `id`, `title`, `description`, `code`, `duration_hours`, `price`, `level`, `instructor`, `is_active`, `timestamps`

### Students Table
- `id`, `first_name`, `last_name`, `email`, `phone`, `date_of_birth`, `address`, `city`, `country`, `education_level`, `is_active`, `timestamps`

### Enrollments Table
- `id`, `student_id`, `course_id`, `enrollment_date`, `amount_paid`, `payment_status`, `enrollment_status`, `completion_date`, `remarks`, `timestamps`

## Frontend Integration

The API is configured to work with frontend applications running on:
- `http://localhost:5173` (Vite default)
- `http://localhost:3000` (React default)
- `http://127.0.0.1:5173`
- `http://127.0.0.1:3000`

## Validation Rules

### Admin
- `name`: required, max 255 characters
- `email`: required, email, unique
- `password`: required, min 8 characters
- `role`: admin or super_admin

### Course
- `title`: required, max 255 characters
- `code`: required, max 50 characters, unique
- `duration_hours`: required, integer, min 1
- `price`: required, numeric, min 0
- `level`: beginner, intermediate, or advanced
- `instructor`: required, max 255 characters

### Student
- `first_name`: required, max 255 characters
- `last_name`: required, max 255 characters
- `email`: required, email, unique
- `phone`: optional, max 20 characters
- `date_of_birth`: optional, date
- Other fields are optional

### Enrollment
- `student_id`: required, exists in students table
- `course_id`: required, exists in courses table
- `enrollment_date`: required, date
- `amount_paid`: required, numeric, min 0
- `payment_status`: pending, paid, failed, or refunded
- `enrollment_status`: active, completed, dropped, or suspended

## Error Responses

- **400**: Validation errors
- **401**: Unauthorized/Invalid credentials
- **404**: Resource not found
- **422**: Unprocessable entity (business logic errors)
- **500**: Server errors

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
php artisan pint
```

### Clear Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Security Features

- Token-based authentication with Sanctum
- Password hashing
- Input validation and sanitization
- CORS configuration
- SQL injection prevention through Eloquent ORM

## License

This project is open-sourced software licensed under the MIT license.
"# ecrbackend" 
"# ecrbackend" 
# ecrbackend
