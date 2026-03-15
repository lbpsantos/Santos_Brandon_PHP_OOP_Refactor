# School Encoding Module - OOP Refactor

This is an Object-Oriented Programming (OOP) refactored version of the School Encoding Module PHP web application. The refactoring follows PSR-1 and PSR-4 standards for improved code organization, maintainability, and reusability.

## Features

- **Authentication & Authorization**: Secure login/logout with session management and role-based access control
- **Subject Management**: Create, read, update, and delete subjects with staff/admin privileges
- **Program Management**: Manage academic programs with staff/admin privileges
- **User Management**: Admin can manage user accounts and roles
- **Password Management**: Users can change their passwords securely
- **Security Features**:
  - Password hashing with BCrypt
  - Prepared statements to prevent SQL injection
  - Session regeneration to prevent session fixation
  - Input validation and sanitization

## Default Admin Credentials

```
Username: admin
Password: admin123
```

> **Important**: Change the admin password on first login!

## Project Structure

```
OOP_Refactor/
├── app/
│   ├── Core/                 # Core application classes
│   │   ├── Database.php      # Singleton database connection handler
│   │   ├── Auth.php          # Authentication and authorization
│   │   └── SessionManager.php # Session management
│   ├── Models/               # Data models for business logic
│   │   ├── User.php          # User CRUD operations
│   │   ├── Subject.php       # Subject CRUD operations
│   │   └── Program.php       # Program CRUD operations
│   └── Helpers/              # Utility helper classes
│       ├── Validator.php     # Input validation
│       ├── Redirect.php      # Redirection utility
│       └── FlashMessage.php  # Flash message handling
├── config/
│   └── config.php            # Application configuration and autoloading
├── public/                   # Public-facing web pages
│   ├── index.php             # Entry point
│   ├── login.php             # Login page
│   ├── logout.php            # Logout page
│   ├── home.php              # Main dashboard
│   ├── subject_list.php      # Subject listing page
│   ├── subject_new.php       # Create subject page
│   ├── subject_edit.php      # Edit subject page
│   ├── subject_delete.php    # Delete subject
│   ├── program_list.php      # Program listing page
│   ├── program_new.php       # Create program page
│   ├── program_edit.php      # Edit program page
│   ├── program_delete.php    # Delete program
│   ├── users_list.php        # User listing page
│   ├── users_new.php         # Create user page
│   ├── users_edit.php        # Edit user page
│   ├── users_delete.php      # Delete user
│   └── change_password.php   # Change password page
├── school.sql                # Database schema and sample data
└── README.md                 # This file
```

## Class Overview

### Core Classes

#### `App\Core\Database`
- **Purpose**: Singleton database connection handler
- **Key Methods**:
  - `getInstance()`: Gets the singleton instance
  - `getConnection()`: Returns the MySQLi connection object
  - `closeConnection()`: Closes the database connection
- **Usage**: 
  ```php
  $db = Database::getInstance();
  $conn = $db->getConnection();
  ```

#### `App\Core\SessionManager`
- **Purpose**: Manages PHP sessions and session data
- **Key Methods**:
  - `start()`: Starts the session if not already started
  - `set($key, $value)`: Sets a session value
  - `get($key, $default = null)`: Gets a session value
  - `has($key)`: Checks if a key exists in session
  - `remove($key)`: Removes a session value
  - `destroy()`: Destroys the entire session
  - `regenerateId($deleteOldSession = true)`: Regenerates session ID
- **Usage**:
  ```php
  SessionManager::start();
  SessionManager::set('user_id', 123);
  $userId = SessionManager::get('user_id');
  ```

#### `App\Core\Auth`
- **Purpose**: Handles authentication, authorization, and access control
- **Key Methods**:
  - `login($username, $password, $connection)`: Authenticates user
  - `logout()`: Logs out the current user
  - `isLoggedIn()`: Checks if user is logged in
  - `getCurrentUser()`: Returns current user data
  - `hasRole($roles)`: Checks if user has specific role(s)
  - `isAdmin()`: Checks if user is admin
  - `isStaffOrAdmin()`: Checks if user is staff or admin
  - `canManageCatalog()`: Checks if user can manage subjects/programs
- **Usage**:
  ```php
  $auth = new Auth();
  if ($auth->isLoggedIn()) {
      $user = $auth->getCurrentUser();
  }
  ```

### Model Classes

#### `App\Models\User`
- **Purpose**: Manages user account operations
- **Key Methods**:
  - `create($username, $accountType, $password, $confirmPassword, $adminId)`: Creates new user
  - `read($options)`: Reads users (single or all)
  - `update($userId, $username, $accountType, $updatedBy)`: Updates user
  - `changePassword($userId, $currentPassword, $newPassword, $confirmPassword)`: Changes password
  - `delete($userId)`: Deletes user
  - `getAccountTypes()`: Returns available account types
- **Account Types**: `admin`, `staff`, `teacher`, `student`
- **Usage**:
  ```php
  $userModel = new User($conn);
  $result = $userModel->create('john', 'student', 'pass123', 'pass123', 1);
  ```

#### `App\Models\Subject`
- **Purpose**: Manages subject catalog operations
- **Key Methods**:
  - `create($code, $title, $unit)`: Creates new subject
  - `read($options)`: Reads subjects (single or all with search/sort)
  - `update($subjectId, $code, $title, $unit)`: Updates subject
  - `delete($subjectId)`: Deletes subject
- **Fields**: `code` (unique), `title`, `unit` (positive integer)
- **Usage**:
  ```php
  $subjectModel = new Subject($conn);
  $result = $subjectModel->create('CS101', 'Intro to CS', 3);
  ```

#### `App\Models\Program`
- **Purpose**: Manages academic program operations
- **Key Methods**:
  - `create($code, $title, $years)`: Creates new program
  - `read($options)`: Reads programs (single or all with search/sort)
  - `update($programId, $code, $title, $years)`: Updates program
  - `delete($programId)`: Deletes program
- **Fields**: `code` (unique), `title`, `years` (1-10)
- **Usage**:
  ```php
  $programModel = new Program($conn);
  $result = $programModel->create('BSCS', 'Bachelor of CS', 4);
  ```

### Helper Classes

#### `App\Helpers\Validator`
- **Purpose**: Provides input validation methods
- **Key Methods**:
  - `validateUsername($username)`: Validates username
  - `validatePassword($password, $minLength = 8)`: Validates password
  - `validatePasswordMatch($password, $confirmPassword)`: Validates password match
  - `validateAccountType($accountType, $allowedTypes)`: Validates account type
  - `validateSubjectCode($code)`: Validates subject code
  - `validateSubjectTitle($title)`: Validates subject title
  - `validateUnit($unit)`: Validates unit number
  - `validateId($id)`: Validates ID is positive integer
- **All methods return**: `['valid' => bool, 'error' => string, 'value' => mixed]`

#### `App\Helpers\Redirect`
- **Purpose**: Simplifies page redirects
- **Key Methods**:
  - `to($url)`: Redirects to URL
  - `toLogin()`: Redirects to login page
  - `toHome()`: Redirects to home page
  - `back($fallback = 'home.php')`: Redirects back to previous page

#### `App\Helpers\FlashMessage`
- **Purpose**: Manages one-time session messages
- **Key Methods**:
  - `set($message, $type = 'info')`: Sets a flash message
  - `get()`: Retrieves and clears flash message
  - `has()`: Checks if flash message exists
  - `success($message)`: Sets success message
  - `error($message)`: Sets error message
  - `info($message)`: Sets info message
  - `warning($message)`: Sets warning message
- **Message Types**: `success`, `error`, `info`, `warning`

## Installation & Setup

### 1. Database Setup
- Import the `school.sql` file into your MySQL database:
  ```sql
  mysql -u root school < school.sql
  ```

### 2. Configuration
- Edit `config/config.php` if needed to change database credentials:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASSWORD', '');
  define('DB_NAME', 'school');
  ```

### 3. Web Server Setup
- Point your web server to the `public/` directory
- Access the application via: `http://localhost/OOP_Refactor/public/`

## Access Control

- **Anonymous Users**: Can access login page only
- **All Logged-in Users**: Can view dashboard and list views
- **Staff/Admin Users**: Can create/edit/view subjects and programs
- **Admin Only**: Can manage users and delete items

## PSR-1 & PSR-4 Compliance

### PSR-1 Compliance
✅ Class names use PascalCase (e.g., `Database`, `SessionManager`, `User`)
✅ Method names use camelCase (e.g., `getConnection()`, `isLoggedIn()`)
✅ File names match class names (Database.php, SessionManager.php)
✅ Constants use UPPERCASE (DB_HOST, DEFAULT_ROLE)

### PSR-4 Compliance
✅ Namespaces match folder structure:
  - `App\Core` → `app/Core/`
  - `App\Models` → `app/Models/`
  - `App\Helpers` → `app/Helpers/`
✅ One class per file
✅ Automatic class loading via PSR-4 autoloader in `config.php`

## Security Features

1. **Password Security**: 
   - Passwords hashed with BCrypt (PASSWORD_BCRYPT)
   - Password verification using `password_verify()`

2. **SQL Injection Prevention**:
   - All database queries use prepared statements
   - Input parameters bound with `bind_param()`

3. **Session Security**:
   - Session ID regenerated on login
   - Session destruction on logout
   - Cache headers prevent back-button access

4. **Input Validation**:
   - All user inputs validated before processing
   - HTML output escaped with `htmlspecialchars()`

5. **Access Control**:
   - Role-based access control (RBAC)
   - Login verification on protected pages
   - Admin-only delete operations

## Development Notes

### Adding New Features
1. Create model class in `app/Models/` if dealing with data
2. Create public page in `public/` for user interface
3. Use DI (dependency injection) by passing Database connection
4. Follow existing code patterns for consistency

### Error Handling
- All model methods return: `['success' => bool, 'error' => string]`
- All validation methods return: `['valid' => bool, 'error' => string]`
- Public pages check success/error and display appropriately

### Namespacing Pattern
```php
use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Helpers\Validator;
use App\Helpers\FlashMessage;
use App\Helpers\Redirect;
```

## Troubleshooting

### Database Connection Failed
- Check database credentials in `config/config.php`
- Ensure MySQL server is running
- Verify database `school` exists

### Login Not Working
- Check that `school.sql` has been imported
- Verify admin user exists with password hash: `$2y$10$HOgDs3wTs0E78kemm3k9F.9oQyziU7g13rNw1Efi7JaXpss8WU5Ka` (password: admin123)

### Classes Not Found
- Ensure namespaces match folder structure
- Check that files are in correct directories
- Clear browser cache if needed

## Migration from Procedural Version

The refactored version maintains all features of the original procedural application while organizing code into reusable classes. Key improvements:

- **Before**: HTML, SQL, sessions, validation all mixed in page files
- **After**: Business logic in classes, pages handle presentation only
- **Result**: More maintainable, testable, and reusable code

## License

This project is provided for educational purposes.

## Author

Santos, Brandon - PHP OOP Refactoring Activity

---

Last Updated: March 12, 2026
