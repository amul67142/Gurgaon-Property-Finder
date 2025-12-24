# Installation Guide - Gurgaon Property Finder

## 📋 Table of Contents
1. [Local Development Setup](#local-development-setup)
2. [Production Deployment](#production-deployment)
3. [Database Setup](#database-setup)
4. [Troubleshooting](#troubleshooting)

---

## 🖥️ Local Development Setup

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP (choose PHP 7.4 or higher)
3. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Clone Repository

```bash
git clone https://github.com/amul67142/Gurgaon-Property-Finder.git
```

### Step 3: Move to htdocs

**Windows:**
```bash
move Gurgaon-Property-Finder C:\xampp\htdocs\ggn
```

**Mac/Linux:**
```bash
mv Gurgaon-Property-Finder /Applications/XAMPP/htdocs/ggn
```

### Step 4: Create Database

1. Open browser: `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Name it: `ggn`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

### Step 5: Import Database (Optional)

If you have a SQL file:
1. Select `ggn` database in phpMyAdmin
2. Click "Import" tab
3. Choose your SQL file
4. Click "Go"

### Step 6: Access Application

Open browser and navigate to:
```
http://localhost/ggn/
```

**That's it!** The database configuration automatically detects you're on localhost and uses the correct credentials.

---

## 🌐 Production Deployment (Hostinger)

### Step 1: Prepare Production Database

1. Login to Hostinger control panel
2. Go to MySQL Databases
3. Create a new database
4. Note down:
   - Database name
   - Username
   - Password

### Step 2: Export Local Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `ggn` database
3. Click "Export" tab
4. Choose "Quick" export method
5. Click "Go" to download `.sql` file

### Step 3: Import to Production

1. Login to Hostinger phpMyAdmin
2. Select your production database
3. Click "Import"
4. Upload the `.sql` file
5. Click "Go"

### Step 4: Upload Files

**Via FTP/SFTP:**
1. Use FileZilla or any FTP client
2. Connect using Hostinger FTP credentials
3. Upload all project files to `public_html/` folder

**Via File Manager:**
1. Zip your project folder
2. Upload via Hostinger File Manager
3. Extract in public_html

### Step 5: Verify Configuration

The `config/db.php` file is already configured to auto-detect production environment! 

**It will automatically use:**
- Host: `localhost`
- Database: `u650869678_gurugaonpro`
- Username: `u650869678_gurugaonpro`  
- Password: `Amul@123#`

**No code changes needed!**

### Step 6: Set Permissions

Via Hostinger File Manager or FTP:
```
uploads/ → 755
config/db.php → 644
```

### Step 7: Test Your Site

Visit your domain: `https://yourdomain.com`

---

## 🗄️ Database Setup

### Creating Admin User

1. Navigate to: `http://your-domain.com/create_admin.php`
2. Fill in admin details
3. Submit to create admin account
4. **Delete `create_admin.php` after use!**

### Database Schema

The application will create tables automatically on first run, or you can import from SQL file.

**Main Tables:**
- `users` - User accounts (admins, brokers)
- `properties` - Property listings
- `amenities` - Property amenities
- `property_amenities` - Property-amenity relationships
- `leads` - Contact form submissions

---

## 🔧 Troubleshooting

### Issue: "Database Connection Failed"

**Solution:**
- Check MySQL is running in XAMPP
- Verify database name is `ggn`
- Ensure credentials are correct in `config/db.php`

### Issue: "Page not found" or 404 errors

**Solution:**
- Check `.htaccess` file exists
- Ensure Apache `mod_rewrite` is enabled
- Verify file permissions (755 for folders, 644 for files)

### Issue: Images not uploading

**Solution:**
```bash
# Set correct permissions
chmod 755 uploads/
```

### Issue: Styles not loading

**Solution:**
- Clear browser cache
- Check if CSS files are accessible
- Verify file paths in HTML

### Issue: Auto-detection not working

**Solution:**
The auto-detection checks `$_SERVER['HTTP_HOST']`:
- **Local**: Should be `localhost`, `127.0.0.1`
- **Production**: Your actual domain name

If issues persist, you can manually set credentials in `config/db.php`

---

## 📞 Need Help?

- **GitHub Issues**: [Open an issue](https://github.com/amul67142/Gurgaon-Property-Finder/issues)
- **Email**: amul67142@gmail.com

---

**Happy Developing! 🚀**
