# Learning Materials Management System

A modern, professional web application for managing and distributing learning materials including PDFs, Word documents, and other educational resources.

## Features

### Public User Features
- **Modern Interface**: Clean, professional design with responsive layout
- **Material Browsing**: Browse learning materials with advanced search and filtering
- **Secure Downloads**: Protected file downloads with tracking
- **Search & Filter**: Advanced search by keywords and file types
- **Professional Design**: Deep blue color scheme with modern UI components

### Admin Features
- **Dashboard**: Comprehensive admin dashboard with statistics
- **Material Management**: Full CRUD operations for learning materials
- **File Upload**: Secure file upload with validation
- **User Authentication**: Secure admin login and session management
- **Analytics**: Download tracking and reporting

### Technical Features
- **Security**: CSRF protection, secure file handling, SQL injection prevention
- **Performance**: Optimized queries, file caching, responsive design
- **Modern PHP**: Object-oriented architecture with proper separation of concerns
- **Database**: MySQL with proper indexing and relationships
- **Professional Code**: PSR standards, error handling, logging

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- PDO PHP extension
- File upload support
- Minimum 128MB memory limit
- 50MB+ upload file size limit

## Installation

### Quick Setup
1. Run the setup script: `http://your-domain/setup.php`
2. Follow the installation wizard
3. Use default admin credentials: `admin / admin123`
4. Change default password immediately

### Manual Installation
1. Import database: `mysql < database/schema.sql`
2. Configure database: Edit `config/database.php`
3. Set file permissions: `chmod 755 uploads/`
4. Create admin user with default credentials

## Configuration

### Database Settings
Edit `config/database.php`:
```php
private const DB_HOST = 'localhost';
private const DB_NAME = 'learning_materials';
private const DB_USER = 'your_username';
private const DB_PASS = 'your_password';
```

### Application Settings
Edit `config/app.php` for:
- Upload file size limits
- Allowed file types
- Currency settings
- Security configurations

## File Structure

```
/
├── admin/              # Admin panel pages
├── assets/             # CSS, JS, images
├── classes/            # PHP classes
├── config/             # Configuration files
├── database/           # Database schema
├── includes/           # Common templates
├── uploads/            # File storage (secured)
├── index.php           # Main homepage
├── search.php          # Advanced search
├── download.php        # Secure download handler
└── setup.php           # Installation script
```

## Security Features

- **CSRF Protection**: All forms protected against CSRF attacks
- **SQL Injection Prevention**: Prepared statements throughout
- **File Upload Security**: Type validation and secure storage
- **Session Management**: Secure session handling
- **Access Control**: Protected admin areas
- **Input Sanitization**: All user input sanitized

## Admin Panel

Access: `http://your-domain/admin/`

Default credentials:
- **Username**: admin
- **Password**: admin123 (change immediately!)

### Admin Functions
- Add new learning materials
- Edit existing materials
- Delete materials (with confirmation)
- View download statistics
- Manage file uploads
- Monitor system status

## API Endpoints

### File Downloads
```
GET /download.php?id={material_id}
```

### Search
```
GET /?search={query}&type={file_type}&page={page}
GET /search.php?search={query}&type={file_type}
```

## File Types Supported

- **PDF**: application/pdf
- **DOC**: application/msword  
- **DOCX**: application/vnd.openxmlformats-officedocument.wordprocessingml.document

## Development

### Adding New Features
1. Create new PHP classes in `classes/`
2. Add database tables via migration
3. Update admin interface as needed
4. Add frontend templates
5. Update routing if required

### Customization
- Modify CSS in `assets/css/`
- Update color scheme via CSS variables
- Add new file types in `config/app.php`
- Extend database schema as needed

## Troubleshooting

### Common Issues
1. **File uploads failing**: Check PHP upload limits and directory permissions
2. **Database connection errors**: Verify credentials in `config/database.php`
3. **Permission denied**: Ensure web server has write access to `uploads/` and `logs/`
4. **Session issues**: Check PHP session configuration

### Error Logs
- Application logs: `logs/YYYY-MM-DD.log`
- PHP errors: Check server error logs
- Database errors: Logged to application logs

## Performance Optimization

- Enable PHP opcache
- Use MySQL query cache
- Implement CDN for static assets
- Enable gzip compression
- Optimize images and files

## Security Best Practices

1. Change default admin credentials immediately
2. Keep PHP and MySQL updated
3. Use HTTPS in production
4. Regular security updates
5. Monitor access logs
6. Backup database regularly

## License

This project is built for educational and professional use. Modify and distribute as needed.

## Support

For technical support or questions about implementation, refer to the documentation or system logs for troubleshooting.

## Version

- **Version**: 1.0.0
- **PHP**: 7.4+
- **MySQL**: 5.7+
- **Framework**: Custom PHP MVC-style architecture
