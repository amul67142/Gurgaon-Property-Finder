# 🏠 Gurgaon Property Finder

A modern, full-featured real estate listing platform built with PHP, MySQL, and Tailwind CSS. Designed specifically for Gurugram (Gurgaon) property market with advanced features like AI-powered investment reports, broker/developer listings, and premium property showcasing.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)

## ✨ Features

### For Users
- 🔍 **Advanced Property Search** - Filter by location, type, status, and price range
- 🏆 **Premium Listings** - Featured properties with luxury dark backgrounds
- 🤖 **AI Investment Reports** - Generate detailed market analysis for properties
- 📱 **Responsive Design** - Optimized for all devices
- 🎨 **Modern UI** - Luxury dark themes with gold and cyan accents
- 📍 **Location-based Search** - Find properties in specific Gurugram sectors

### For Brokers/Developers
- 📊 **Broker Dashboard** - Manage your property listings
- ➕ **Easy Property Submission** - Add properties with images and amenities
- ✏️ **Edit Listings** - Update property details anytime
- 🎯 **Custom Amenities** - Add unique amenities (comma-separated)
- 📈 **Performance Tracking** - Monitor your listings
- 👤 **Profile Management** - Customize your broker profile

### For Admins
- 👨‍💼 **Admin Dashboard** - Comprehensive management panel
- ✅ **Property Approval** - Review and approve listings
- 🏷️ **Featured Management** - Promote premium properties
- 👥 **User Management** - Manage brokers and users
- 📊 **Analytics** - Track site performance
- 🎛️ **CTA Management** - Manage call-to-action buttons

## 🚀 Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Tailwind CSS
- **Icons**: Font Awesome 6
- **Animations**: AOS (Animate On Scroll)
- **Server**: Apache (XAMPP for local development)

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server
- Composer (optional, for dependencies)
- Modern web browser

## 🛠️ Installation

### Local Development Setup

1. **Clone the repository**
```bash
git clone https://github.com/amul67142/Gurgaon-Property-Finder.git
cd Gurgaon-Property-Finder
```

2. **Set up XAMPP**
   - Install [XAMPP](https://www.apachefriends.org/)
   - Start Apache and MySQL services

3. **Move project to htdocs**
```bash
# Move the project folder to XAMPP's htdocs directory
# Windows: C:\xampp\htdocs\ggn
# Mac/Linux: /Applications/XAMPP/htdocs/ggn
```

4. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `ggn`
   - Import the database schema (if provided) or let the application create tables

5. **Configure Database** *(Already configured with auto-detection!)*
   - The `config/db.php` file automatically detects your environment
   - Local: Uses `root` with no password
   - Production: Uses your Hostinger credentials
   - **No manual changes needed!**

6. **Access the Application**
```
http://localhost/ggn/
```

### Production Deployment (Hostinger)

1. **Upload Files**
   - Upload all files via FTP/SFTP to your hosting directory

2. **Database Setup**
   - Export your local database from phpMyAdmin
   - Import to your production database via Hostinger's phpMyAdmin

3. **Auto-Configuration**
   - The `config/db.php` will automatically use production credentials
   - No code changes needed!

4. **Set Permissions**
```bash
# Set proper permissions
chmod 755 uploads/
chmod 644 config/db.php
```

## 📁 Project Structure

```
ggn/
├── admin/              # Admin panel
│   ├── dashboard.php
│   ├── approve_properties.php
│   └── manage_users.php
├── api/                # API endpoints
│   ├── add_amenity.php
│   ├── submit_lead.php
│   └── generate_investment_report.php
├── broker/             # Broker dashboard
│   ├── dashboard.php
│   ├── add_property.php
│   └── edit_property.php
├── config/             # Configuration files
│   ├── db.php          # Auto-detecting database config
│   └── config.php
├── includes/           # Reusable components
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── tools/              # Utility scripts
├── uploads/            # User uploaded files
├── index.php           # Homepage
├── properties.php      # Property listings
├── property-details.php
├── about-us.php
├── contact.php
├── register.php
└── login.php
```

## 🔐 Default Credentials

### Admin Access
- **Email**: admin@ggn.com (Create via `create_admin.php`)
- **Password**: Set during creation

### Database (Local)
- **Host**: localhost
- **Database**: ggn
- **Username**: root
- **Password**: (empty)

## 🎨 Key Features Explained

### Auto Environment Detection
The application automatically detects whether it's running locally or in production:
- **Local (localhost)**: Uses XAMPP credentials
- **Production**: Uses Hostinger credentials
- **No manual switching required!**

### Premium Featured Listings
- Gold-themed dark background with dot pattern
- Premium badge with crown icon
- Enhanced shadows and hover effects
- Larger images and gold buttons

### Standard Listings
- Cyan-themed dark background with grid pattern
- Modern card design with backdrop blur
- Cyan accent colors and smooth animations

### AI Investment Reports
- Analyze property potential
- Market trends and location insights
- Automated report generation

## 🚧 Development

### Adding Custom Amenities
Brokers can add custom amenities by:
1. Going to Add/Edit Property page
2. Entering comma-separated amenities in the custom field
3. Clicking "Add to List"
4. Amenities are saved with default tick icon

### Modifying Styles
- Main styles: Tailwind CSS (inline classes)
- Custom CSS: Add to relevant section or create new CSS file
- Color scheme: Defined in Tailwind config

## 📝 Environment Variables

The `config/db.php` file uses `$_SERVER['HTTP_HOST']` to detect environment:
- Checks if hostname is `localhost` or `127.0.0.1`
- Automatically switches credentials
- Secure and convenient!

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📜 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Author

**Amul**
- GitHub: [@amul67142](https://github.com/amul67142)

## 🙏 Acknowledgments

- Tailwind CSS for the styling framework
- Font Awesome for icons
- AOS library for scroll animations
- All contributors and users!

## 📞 Support

For support, email amul67142@gmail.com or open an issue on GitHub.

---

**Made with ❤️ for Gurugram's Real Estate Market**
