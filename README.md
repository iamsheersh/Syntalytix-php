<div align="center">

# 🎓 Syntalytix LMS

### *A Modern Learning Management System*

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

**Pure PHP • No Frameworks • No Firebase • Modern UI**

[🚀 Get Started](#-quick-start) • [📖 Documentation](#-documentation) • [✨ Features](#-features)

</div>

---

## 🌟 Overview

**Syntalytix LMS** is a feature-rich Learning Management System built with pure PHP, HTML, CSS, and JavaScript. Designed for educational institutions, it provides a seamless experience for administrators, teachers, and students with a modern, responsive interface supporting both light and dark themes.

<div align="center">

| 👨‍💼 Admin | 👨‍🏫 Teacher | 👨‍🎓 Student |
|:---:|:---:|:---:|
| Full System Control | Content Management | Learn & Test |
| User Management | Create Tests | Track Progress |
| Analytics Dashboard | Upload Materials | Interactive Learning |

</div>

---

## ✨ Features

### 🎨 Modern UI/UX
- 🌓 **Dual Theme Support** — Seamless light/dark mode toggle
- 📱 **Fully Responsive** — Works on desktop, tablet, and mobile
- ⚡ **Real-time Updates** — Dynamic content without page reloads
- 🎯 **Interactive Elements** — Smooth animations and transitions

### 🔐 Security First
- 🔒 **Password Hashing** — bcrypt encryption for all passwords
- 🛡️ **SQL Injection Protection** — Prepared statements throughout
- 🚫 **XSS Protection** — Output sanitization
- 🔑 **Session Management** — Secure authentication system
- ✅ **Strong Password Policy** — Real-time validation with visual feedback

### 📊 Dashboard Highlights

| Feature | Admin | Teacher | Student |
|---------|:-----:|:-------:|:-------:|
| System Statistics | ✅ | ❌ | ❌ |
| User Management | ✅ | ❌ | ❌ |
| Content Creation | ✅ | ✅ | ❌ |
| Test Management | ✅ | ✅ | ❌ |
| Take Tests | ❌ | ❌ | ✅ |
| View Progress | ✅ | ✅ | ✅ |
| Dark Mode | ✅ | ✅ | ✅ |

---

## 🚀 Quick Start

### Prerequisites

```
PHP 7.4+    MySQL 5.7+    Apache/Nginx    Modern Browser
```

### 📦 Installation

#### 1️⃣ Clone & Setup
```bash
# Clone the repository
git clone https://github.com/yourusername/syntalytix-lms.git
cd syntalytix-lms

# Or download and extract to your web root
# Place in: /var/www/html/lms-php (Linux) or C:/xampp/htdocs/lms-php (Windows)
```

#### 2️⃣ Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE lms_db;"

# Import schema
mysql -u root -p lms_db < database/schema.sql
```

#### 3️⃣ Configuration
Edit `config/database.php`:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'lms_db');
?>
```

#### 4️⃣ Web Server
**Apache:** Enable `mod_rewrite`
**Nginx:** Configure PHP-FPM

#### 5️⃣ Create Admin User

**Option A:** Register via signup page, then promote:
```sql
UPDATE users SET role_id = 1 WHERE email = 'your_email@example.com';
```

**Option B:** Direct SQL insert:
```sql
INSERT INTO users (name, email, password, role_id, status) VALUES 
('Admin', 'admin@lms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Active');
-- Default password: password
```

🎉 **Done!** Visit `http://localhost/lms-php/`

---

## 📁 Project Structure

```
lms-php/
├── 📂 api/                          # REST API Endpoints
│   ├── 🔐 auth.php                  # Authentication APIs
│   ├── 👑 admin.php                 # Admin operations
│   ├── 👨‍🏫 teacher.php              # Teacher operations
│   └── 👨‍🎓 student.php             # Student operations
│
├── 📂 config/
│   └── 🔌 database.php              # Database configuration
│
├── 📂 includes/
│   └── 🎫 session.php               # Session management
│
├── 📂 database/
│   └── 🗄️ schema.sql                # Database schema
│
├── 📂 pages/                        # Application Pages
│   ├── 🔑 login.php                 # Login page
│   ├── 📝 signup.php                # Registration page
│   ├── 👑 admin_dashboard.php      # Admin dashboard
│   ├── 👨‍🏫 teacher_dashboard.php   # Teacher dashboard
│   └── 👨‍🎓 student_dashboard.php   # Student dashboard
│
├── 📂 assets/                       # Static assets
├── 🚪 index.php                   # Landing page / Entry point
└── 📖 README.md                   # This file
```

---

## 🔌 API Documentation

### Authentication Endpoints
| Method | Endpoint | Action | Description |
|--------|----------|--------|-------------|
| POST | `api/auth.php` | `login` | User authentication |
| POST | `api/auth.php` | `register` | New user registration |
| POST | `api/auth.php` | `logout` | End session |
| GET | `api/auth.php` | `check_registration` | Verify registration status |

### Admin Endpoints
| Method | Endpoint | Action | Description |
|--------|----------|--------|-------------|
| GET | `api/admin.php` | `get_users` | List all users |
| GET | `api/admin.php` | `get_content` | List all content |
| GET | `api/admin.php` | `get_tests` | List all tests |
| GET | `api/admin.php` | `get_stats` | System statistics |
| POST | `api/admin.php` | `create_user` | Add new user |
| POST | `api/admin.php` | `update_user` | Modify user |
| POST | `api/admin.php` | `create_content` | Add content |
| POST | `api/admin.php` | `create_test` | Create test |

---

## 🛠️ Tech Stack

<div align="center">

| Technology | Purpose | Version |
|------------|---------|---------|
| **PHP** | Backend Logic | 7.4+ |
| **MySQL** | Database | 5.7+ |
| **HTML5** | Structure | - |
| **CSS3** | Styling | - |
| **JavaScript** | Interactivity | ES6+ |
| **Apache/Nginx** | Web Server | - |

</div>

---

## 🎨 Theme Support

The application features a sophisticated dual-theme system:

| Light Mode | Dark Mode |
|:----------:|:---------:|
| Clean & Professional | Easy on the eyes |
| Perfect for daytime | Ideal for low-light |
| High contrast | Reduced eye strain |

**🌓 Toggle:** Click the moon/sun icon in the top-right corner. Your preference is saved in localStorage.

---

## 🌐 Browser Support

| Browser | Version | Status |
|---------|---------|:------:|
| Chrome/Edge | 90+ | ✅ |
| Firefox | 88+ | ✅ |
| Safari | 14+ | ✅ |
| Mobile Chrome | Latest | ✅ |
| Mobile Safari | Latest | ✅ |

---

## 🐛 Troubleshooting

### ❌ Database Connection Error
```
✓ Check config/database.php credentials
✓ Ensure MySQL service is running
✓ Verify database exists
```

### ❌ 404 Errors
```
✓ Check .htaccess is present
✓ Enable mod_rewrite (Apache)
✓ Set correct file permissions (644 files, 755 dirs)
```

### ❌ Session Issues
```
✓ Enable PHP sessions in php.ini
✓ Ensure session.save_path is writable
✓ Clear browser cookies
```

---

## 📸 Screenshots

<div align="center">

*Dashboard previews coming soon*

<!-- Add screenshots here -->
<!-- ![Admin Dashboard](screenshots/admin.png) -->
<!-- ![Teacher Dashboard](screenshots/teacher.png) -->
<!-- ![Student Dashboard](screenshots/student.png) -->

</div>

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. 🍴 Fork the repository
2. 🌿 Create a feature branch (`git checkout -b feature/amazing-feature`)
3. 💾 Commit changes (`git commit -m 'Add amazing feature'`)
4. 📤 Push to branch (`git push origin feature/amazing-feature`)
5. 🔀 Open a Pull Request

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

```
Copyright (c) 2026 Syntalytix

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions...
```

---

## 📞 Support

Need help? We're here for you!

| Channel | Link |
|---------|------|
| 🐛 Issues | [GitHub Issues](https://github.com/iamsheersh/syntalytix-php/issues) |
| 📧 Email | syntalytix@gmail.com |
| 💬 Discussions | [GitHub Discussions](https://github.com/iamsheersh/syntalytix-php/discussions) |

---

<div align="center">

**⭐ Star this repo if you find it helpful!**

Made with ❤️ by Sheersh

</div>
