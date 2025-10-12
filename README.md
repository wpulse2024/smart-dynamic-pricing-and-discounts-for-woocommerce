# My Plugin - WordPress Plugin with Laravel Lite Architecture

A modern WordPress plugin boilerplate that mimics Laravel's architecture, featuring a clean MVC structure, Vue 3 admin interface, and comprehensive tooling.

## 🏗️ Architecture

This plugin follows a Laravel-inspired architecture with:

- **Models**: Lightweight ORM using WordPress `$wpdb`
- **Controllers**: Handle request logic and responses
- **Routes**: RESTful API routing system
- **Database**: Migration-like helper for table management
- **Services**: Dependency injection container
- **Middleware**: Request filtering and authentication

## 📁 Project Structure

```
my-plugin/
├── app/
│   ├── Models/           # Eloquent-style models
│   ├── Controllers/      # Request handlers
│   ├── Routes/          # API route definitions
│   ├── Database/        # Migrations and seeders
│   ├── Services/        # Business logic services
│   ├── Middleware/      # Request middleware
│   └── Core/           # Core plugin classes
├── resources/
│   ├── js/             # JavaScript entry points
│   └── vue/            # Vue 3 components
├── public/             # Built assets
├── includes/           # WordPress-specific includes
├── vendor/             # Composer dependencies
├── smart-dynamic-pricing-and-discounts-for-woocommerce.php       # Main plugin file
├── composer.json       # PHP dependencies
├── package.json        # Node.js dependencies
└── vite.config.js      # Build configuration
```

## 🚀 Getting Started

### Prerequisites

- PHP 7.4+
- Node.js 16+
- WordPress 5.0+
- Composer
- WP-CLI (optional)

### Installation

1. **Clone or download the plugin**
   ```bash
   # If using git
   git clone <repository-url> my-plugin
   cd my-plugin
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Build assets**
   ```bash
   npm run build
   # or for development with hot reload
   npm run dev
   ```

5. **Activate the plugin**
   - Upload to `/wp-content/plugins/`
   - Activate in WordPress admin

## 🛠️ Development

### Building Assets

```bash
# Development build with hot reload
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

### Database Migrations

The plugin includes a migration system for database management:

```php
// Create a new migration
class CreateTripsTable extends Migration
{
    public function up()
    {
        $this->database->createTable('trips', [
            'id' => 'bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT',
            'title' => 'varchar(255) NOT NULL',
            // ... more columns
        ]);
    }
}
```

### Creating Models

```php
use MyPlugin\Models\Model;

class Trip extends Model
{
    protected $fillable = ['title', 'destination', 'price'];
    
    public static function getUpcoming()
    {
        return self::where('start_date', '>', date('Y-m-d'));
    }
}
```

### Creating Controllers

```php
use MyPlugin\Controllers\Controller;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::all();
        $this->success(['trips' => $trips]);
    }
    
    public function store()
    {
        $data = $this->validate([
            'title' => 'required|max:255',
            'price' => 'required|numeric'
        ]);
        
        $trip = Trip::create($data);
        $this->success(['trip' => $trip], 'Trip created');
    }
}
```

### API Routes

```php
// In app/Routes/ApiRoutes.php
$router->group(['prefix' => '/api'], function($router) {
    $router->get('/trips', 'TripController@index');
    $router->post('/trips', 'TripController@store', ['auth']);
    $router->get('/trips/{id}', 'TripController@show');
});
```

## 🎨 Vue 3 Admin Interface

The plugin includes a modern Vue 3 admin interface with:

- **Component-based architecture**
- **Reactive data binding**
- **Modern UI components**
- **API integration**
- **Form validation**

### Vue Components

- `App.vue` - Main application component
- `TripsView.vue` - Trip management interface
- `SettingsView.vue` - Plugin settings

## 🔧 WP-CLI Commands

### Rename Plugin

```bash
wp myplugin rename --slug=new-plugin --namespace=NewPlugin --author="Your Name" --desc="New Description"
```

This command will:
- Update plugin header information
- Change namespace across all PHP files
- Update text domain references
- Modify composer.json and package.json

## 📚 API Endpoints

### Trips API

- `GET /wp-json/my-plugin/v1/trips` - List all trips
- `GET /wp-json/my-plugin/v1/trips/{id}` - Get specific trip
- `POST /wp-json/my-plugin/v1/trips` - Create new trip
- `PUT /wp-json/my-plugin/v1/trips/{id}` - Update trip
- `DELETE /wp-json/my-plugin/v1/trips/{id}` - Delete trip
- `GET /wp-json/my-plugin/v1/trips/upcoming` - Get upcoming trips
- `GET /wp-json/my-plugin/v1/trips/past` - Get past trips

### Authentication

Protected endpoints require WordPress authentication. Include the nonce in requests:

```javascript
fetch('/wp-json/my-plugin/v1/trips', {
    headers: {
        'X-WP-Nonce': myPlugin.nonce
    }
})
```

## 🎯 Features

### Core Features

- ✅ **MVC Architecture** - Clean separation of concerns
- ✅ **PSR-4 Autoloading** - Modern PHP class loading
- ✅ **Service Container** - Dependency injection
- ✅ **Database Migrations** - Version-controlled schema changes
- ✅ **RESTful API** - Modern API endpoints
- ✅ **Vue 3 Admin** - Modern admin interface
- ✅ **WP-CLI Integration** - Command-line tools
- ✅ **Middleware Support** - Request filtering
- ✅ **Validation** - Input validation system

### Example Implementation

The plugin includes a complete **Trip Management System** as an example:

- Trip model with relationships
- Trip controller with CRUD operations
- Vue admin interface for trip management
- API endpoints for frontend integration
- Database migration and seeder

## 🔒 Security

- Nonce verification for AJAX requests
- Capability checks for admin functions
- Input validation and sanitization
- SQL injection prevention via prepared statements

## 📝 License

GPL-2.0-or-later

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions, please open an issue in the repository.

---

**Built with ❤️ for the WordPress community**
