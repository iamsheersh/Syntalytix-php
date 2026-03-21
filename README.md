# LMS Portal - PHP Version

A Learning Management System built with HTML, CSS, JavaScript, and PHP (no React, no Firebase).

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Modern web browser

## Installation

### 1. Database Setup

1. Create a MySQL database named `lms_db`
2. Import the schema from `database/schema.sql`:
   ```bash
   mysql -u root -p lms_db < database/schema.sql
   ```

### 2. Configuration

1. Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'lms_db');
   ```

### 3. Web Server Setup

**Apache:**
- Ensure `mod_rewrite` is enabled
- Place files in your web root (e.g., `htdocs/lms-php/`)

**Nginx:**
- Configure root to point to the project folder
- Set up PHP-FPM for PHP processing

### 4. Default Login

After installation, you'll need to create an admin user. You can:

1. Register as a student first via the signup page
2. Manually update the user role in the database:
   ```sql
   UPDATE users SET role_id = 1 WHERE email = 'your_email@example.com';
   ```

Or insert an admin directly:
```sql
INSERT INTO users (name, email, password, role_id) VALUES 
('Admin', 'admin@lms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
```
(Default password: `password`)

## Project Structure

```
lms-php/
├── api/                    # API endpoints
│   ├── auth.php           # Authentication (login, register, logout)
│   ├── admin.php          # Admin operations
│   ├── teacher.php        # Teacher operations
│   └── student.php        # Student operations
├── config/                # Configuration files
│   └── database.php       # Database connection
├── includes/              # Shared PHP files
│   └── session.php        # Session management
├── database/              # Database files
│   └── schema.sql         # Database schema
├── pages/                 # Main application pages
│   ├── login.php          # Login page
│   ├── signup.php         # Registration page
│   ├── admin_dashboard.php    # Admin dashboard
│   ├── teacher_dashboard.php  # Teacher dashboard
│   └── student_dashboard.php  # Student dashboard
├── index.php              # Entry point (redirects to login)
└── README.md              # This file
```

## Features

### Admin Dashboard
- View system statistics (students, teachers, content, tests)
- Manage users (edit roles, status)
- Manage all content and tests
- Configure platform settings
- Toggle student registration

### Teacher Dashboard
- Upload study materials (YouTube videos, PDFs)
- Create tests with questions
- Manage own content and tests
- View topics

### Student Dashboard
- Browse study materials by topic
- Watch YouTube videos inline
- Download/view PDF documents
- Take tests
- View test history and scores

### Security Features
- Password hashing (bcrypt)
- Session-based authentication
- Role-based access control
- SQL injection prevention (prepared statements)
- XSS protection

## API Endpoints

### Authentication (`api/auth.php`)
- `POST action=login` - User login
- `POST action=register` - User registration
- `POST action=logout` - Logout
- `GET action=check_registration` - Check if registration is enabled

### Admin (`api/admin.php`)
- `GET action=get_users` - List all users
- `GET action=get_content` - List all content
- `GET action=get_tests` - List all tests
- `GET action=get_topics` - List all topics
- `GET action=get_stats` - Get dashboard statistics
- `GET action=get_settings` - Get platform settings
- `POST action=update_settings` - Update settings
- `POST action=update_user` - Update user
- `POST action=update_content` - Update content
- `POST action=update_test` - Update test
- `POST action=delete_content` - Delete content
- `POST action=delete_test` - Delete test

### Teacher (`api/teacher.php`)
- `GET action=get_my_content` - Get teacher's content
- `GET action=get_my_tests` - Get teacher's tests
- `GET action=get_topics` - Get all topics
- `POST action=create_content` - Create content
- `POST action=create_test` - Create test with questions
- `POST action=update_content` - Update own content
- `POST action=update_test` - Update own test
- `POST action=delete_content` - Delete own content
- `POST action=delete_test` - Delete own test

### Student (`api/student.php`)
- `GET action=get_materials` - Get study materials
- `GET action=get_tests` - Get available tests
- `GET action=get_test&id=X` - Get test questions
- `POST action=submit_test` - Submit test answers
- `GET action=get_test_history` - Get test history
- `GET action=get_video_progress` - Get video watch progress
- `POST action=save_video_progress` - Save video progress

## Theme Support

The application supports both light and dark themes:
- Toggle with the moon/sun button in the top right
- Theme preference is saved in localStorage

## Browser Compatibility

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Database Connection Error
- Check `config/database.php` credentials
- Ensure MySQL is running
- Verify database exists

### 404 Errors
- Check if `.htaccess` is present and mod_rewrite is enabled
- Verify file permissions (644 for files, 755 for directories)

### Session Issues
- Ensure PHP sessions are enabled
- Check session save path is writable

## License

This project is open source. Feel free to use and modify as needed.

## Support

For issues or questions:
1. Check the browser console for JavaScript errors
2. Check Apache/Nginx error logs for PHP errors
3. Verify database connection settings
4. Ensure all files have correct permissions
