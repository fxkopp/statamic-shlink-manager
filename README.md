# Shlink Manager for Statamic

Manage your [Shlink](https://shlink.io) short URLs directly from the Statamic Control Panel.

## Features

- **List, create, edit, and delete** short URLs
- **Dynamic tag input** with existing tag suggestions and inline creation
- **Advanced options**: title, valid since/until, max visits, crawlable, forward query params
- **Visit statistics** with country, referer, and bot filtering
- **QR code generation** for every short URL
- **Dashboard widget** showing recent links and total visit count
- **Shlink Web Client integration** with servers.csv export (super admins only)
- **Status badges**: expired, max reached, scheduled
- **Permissions**: `view short urls`, `edit short urls`
- **Translations**: English and German

## Requirements

- PHP 8.4+
- Statamic 6.10+
- A running [Shlink](https://shlink.io) instance

## Installation

```bash
composer require fxkopp/statamic-shlink-manager
```

## Configuration

Add these environment variables to your `.env`:

```env
SHLINK_BASE_URL=https://s.example.com
SHLINK_API_KEY=your-shlink-api-key
SHLINK_DEFAULT_DOMAIN=s.example.com
```

Generate an API key on your Shlink instance:

```bash
shlink api-key:generate --name="Statamic"
```

### Dashboard Widget

Add the widget to `config/statamic/cp.php`:

```php
'widgets' => [
    ['type' => 'shlink_manager_recent_links', 'width' => 50],
],
```

### Publish Config (optional)

```bash
php artisan vendor:publish --tag=shlink-manager-config
```

## Usage

1. Log in to the Statamic Control Panel
2. Navigate to **Tools > Short URLs**
3. Create, edit, and manage your short URLs
4. View visit statistics and QR codes on the detail page

## Permissions

The addon registers two permissions:

| Permission | Description |
|------------|-------------|
| `view short urls` | View the short URL listing and statistics |
| `edit short urls` | Create, edit, and delete short URLs |

## License

MIT License. See [LICENSE](LICENSE) for details.
