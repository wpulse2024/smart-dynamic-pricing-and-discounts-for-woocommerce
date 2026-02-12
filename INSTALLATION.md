# Installation Guide

## Quick Start

1. **Download/Clone the plugin**
   ```bash
   # Extract to your WordPress plugins directory
   # /wp-content/plugins/my-plugin/
   ```

2. **Install dependencies**
   ```bash
   cd my-plugin
   composer install
   npm install
   ```

3. **Build assets**
   ```bash
   npm run build
   ```

4. **Activate the plugin**
   - Go to WordPress Admin → Plugins
   - Find "My Plugin" and click "Activate"

5. **Access the admin interface**
   - Go to WordPress Admin → My Plugin
   - You'll see the Vue 3 admin interface

## Development Setup

### For Development with Hot Reload

```bash
npm run dev
```

This will start Vite in development mode with hot reloading.

### For Production

```bash
npm run build
```

This will create optimized production assets.

## Database Setup

The plugin will automatically create the necessary database tables when activated. If you want to add sample data:

```php
// Add this to your theme's functions.php temporarily
add_action('init', function() {
    if (current_user_can('manage_options')) {
        $seeder = new \MyPlugin\Database\Seeders\TripSeeder();
        $seeder->run();
    }
});
```

## Using Shortcodes

The plugin includes shortcodes for displaying trips on the frontend:

```
[wpulse-pricing-rules_trips limit="5" status="active"]
[wpulse-pricing-rules_upcoming_trips limit="3"]
```

## API Usage

The plugin provides REST API endpoints:

```javascript
// Get all trips
fetch('/wp-json/my-plugin/v1/trips')

// Create a new trip (requires authentication)
fetch('/wp-json/my-plugin/v1/trips', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': myPlugin.nonce
    },
    body: JSON.stringify({
        title: 'New Trip',
        destination: 'Paris',
        start_date: '2024-06-01',
        end_date: '2024-06-07',
        price: 2500,
        status: 'active'
    })
})
```

## WP-CLI Commands

```bash
# Rename the plugin
wp myplugin rename --slug=new-plugin --namespace=NewPlugin --author="Your Name" --desc="New Description"
```

## Troubleshooting

### Assets not loading
- Make sure you've run `npm run build`
- Check that the `public/` directory has the built files
- Verify file permissions

### Database errors
- Check WordPress database connection
- Ensure the plugin has proper permissions
- Check for plugin conflicts

### Vue components not rendering
- Check browser console for JavaScript errors
- Verify that Vue 3 is loading correctly
- Ensure the admin page has the correct container element

## Customization

### Adding New Models

1. Create a new model in `app/Models/`
2. Extend the base `Model` class
3. Define your table structure and relationships

### Adding New Controllers

1. Create a new controller in `app/Controllers/`
2. Extend the base `Controller` class
3. Add your route handlers

### Adding New Vue Components

1. Create components in `resources/vue/components/`
2. Import and use them in your main Vue app
3. Rebuild assets with `npm run build`

## Support

For issues and questions, please check the main README.md file or open an issue in the repository.
