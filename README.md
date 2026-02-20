# UbeeDev Admin Bundle

A full-featured Symfony bundle for admin panel management, built on top of **EasyAdmin 4**. It provides user management, a media library, Markdown support, and internationalization (EN/FR) out of the box.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Import Routes](#import-routes)
  - [Configure Security](#configure-security)
- [Administrator Management](#administrator-management)
  - [Create the AdminUser Entity](#create-the-adminuser-entity)
  - [Administrator CRUD](#administrator-crud)
  - [Available Roles](#available-roles)
- [Media Library](#media-library)
  - [Media CRUD](#media-crud)
  - [File Upload](#file-upload)
  - [Supported Formats](#supported-formats)
  - [Private vs Public Media](#private-vs-public-media)
- [Custom Forms and Fields](#custom-forms-and-fields)
  - [MediaUploadField (EasyAdmin)](#mediauploadfield-easyadmin)
  - [MediaUploadType (Symfony Form)](#mediauploadtype-symfony-form)
  - [Custom Data Types](#custom-data-types)
- [Twig Extensions](#twig-extensions)
- [Internationalization](#internationalization)
- [Markdown Preview](#markdown-preview)
- [Architecture](#architecture)
- [License](#license)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | >= 8.4 |
| Symfony | 6.x / 7.x |
| EasyAdmin Bundle | >= 4.24 |
| ubee-dev/lib-bundle | ^2.0 |

---

## Installation

```bash
composer require ubee-dev/admin-bundle
```

If the bundle is not auto-registered by Symfony Flex, add it manually:

```php
// config/bundles.php
return [
    // ...
    UbeeDev\AdminBundle\UbeeDevAdminBundle::class => ['all' => true],
];
```

---

## Configuration

### Import Routes

Add the bundle routes to your application:

```yaml
# config/routes/ubee_dev_admin.yaml
ubee_dev_admin:
    resource: '@UbeeDevAdminBundle/config/routing/admin.yaml'
```

This registers the following routes:

| Route | Path | Description |
|---|---|---|
| `ubee_dev_admin_markdown_documentation` | `/admin/markdown-documentation` | Markdown documentation page |
| `ubee_dev_admin_preview_markdown` | `/admin/markdown/preview` | Markdown preview endpoint (AJAX) |

Controller routes (login, CRUD, media) are declared via PHP attributes directly in the controllers.

### Configure Security

Add the required security configuration in `security.yaml`:

```yaml
# config/packages/security.yaml
security:
    password_hashers:
        App\Entity\AdminUser: auto

    providers:
        admin_user_provider:
            entity:
                class: App\Entity\AdminUser
                property: email

    firewalls:
        admin:
            pattern: ^/admin
            lazy: true
            provider: admin_user_provider
            form_login:
                login_path: security_admin_login
                check_path: security_admin_login
                default_target_path: admin_dashboard
            logout:
                path: security_admin_logout
                target: security_admin_login

    access_control:
        - { path: ^/admin/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

---

## Administrator Management

### Create the AdminUser Entity

The bundle provides an abstract `AdminUser` class (MappedSuperclass). You must extend it in your application:

```php
<?php
// src/Entity/AdminUser.php
namespace App\Entity;

use UbeeDev\AdminBundle\Entity\AdminUser as BaseAdminUser;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'admin_user')]
class AdminUser extends BaseAdminUser
{
    // Add your custom fields here if needed
}
```

**Fields provided by the abstract class:**

| Field | Type | Description |
|---|---|---|
| `id` | `int` | Auto-incremented identifier |
| `email` | `string` | Unique email (login identifier) |
| `firstName` | `Name` | First name (custom lib-bundle type) |
| `lastName` | `Name` | Last name (custom lib-bundle type) |
| `roles` | `array<string>` | User roles |
| `password` | `string` | Hashed password |
| `plainPassword` | `string\|null` | Plain-text password (not persisted) |

The class implements Symfony's `UserInterface` and `PasswordAuthenticatedUserInterface`. Every user is automatically granted `ROLE_USER` and `ROLE_ADMIN`.

### Administrator CRUD

The `AdminUserCrudController` is ready to use. Register it in your EasyAdmin dashboard:

```php
// src/Controller/Admin/DashboardController.php
use UbeeDev\AdminBundle\Controller\Admin\AdminUserCrudController;

class DashboardController extends AbstractDashboardController
{
    public function configureMenuItems(): iterable
    {
        // ...
        yield MenuItem::linkToCrud('Administrators', 'fa fa-users', AdminUser::class)
            ->setController(AdminUserCrudController::class);
    }
}
```

**CRUD features:**

- List administrators (id, first name, last name, email, roles)
- Create an administrator with automatic password hashing
- Edit an administrator (password is only changed when a new one is provided)
- Delete an administrator

### Available Roles

| Role | Description |
|---|---|
| `ROLE_USER` | Automatically granted to all users |
| `ROLE_ADMIN` | Automatically granted to all administrators |
| `ROLE_SUPER_ADMIN` | Super administrator (manually assigned) |

---

## Media Library

### Media CRUD

The `MediaCrudController` provides complete media library management. Register it in your dashboard:

```php
use UbeeDev\AdminBundle\Controller\Admin\MediaCrudController;
use App\Entity\Media;

yield MenuItem::linkToCrud('Media Library', 'fa fa-photo-video', Media::class)
    ->setController(MediaCrudController::class);
```

**Available actions on the list page:**

| Action | Icon | Description |
|---|---|---|
| Upload | `fa-upload` | Upload a new file |
| View | `fa-eye` | View media with preview |
| Download | `fa-download` | Download the original file |
| Edit Meta | `fa-edit` | Edit alt text and title |
| Delete | - | Delete the media and its physical file |

The list is paginated (20 items per page) and sorted by creation date (newest first).

### File Upload

File upload is handled through a dedicated interface that allows you to:

- Select a file from your system
- Provide an **alt text** (accessibility)
- Provide a **title**
- Mark the file as **private**

Metadata (MIME type, file size, dimensions for images) is extracted automatically.

### Supported Formats

| Category | Types |
|---|---|
| Images | JPEG, PNG, GIF, SVG, WebP, etc. |
| Videos | MP4, WebM, AVI, etc. |
| Audio | MP3, WAV, OGG, etc. |
| Documents | PDF, DOC, DOCX, etc. |
| Other | Any file type |

### Private vs Public Media

- **Public**: the file is accessible via a web URL. The URL can be copied to the clipboard from the detail view.
- **Private**: the file is not publicly accessible. Any access attempt returns a 403 error.

---

## Custom Forms and Fields

### MediaUploadField (EasyAdmin)

A helper class for quickly creating media upload fields in your EasyAdmin CRUD controllers:

```php
use UbeeDev\AdminBundle\Form\Field\MediaUploadField;

public function configureFields(string $pageName): iterable
{
    // Image upload with metadata (alt + title)
    yield MediaUploadField::new('headerImageUpload', 'Header Image', 'articles');

    // Upload with full description
    yield MediaUploadField::withDescription('bannerUpload', 'Banner', 'banners');

    // Simple upload without metadata
    yield MediaUploadField::simple('thumbnailUpload', 'Thumbnail', 'thumbnails');

    // Upload for all file types
    yield MediaUploadField::forAllFiles('documentUpload', 'Document', 'documents');

    // Upload specifically for videos
    yield MediaUploadField::forVideo('videoUpload', 'Video', 'videos');
}
```

**Naming convention:** the field name must end with `Upload`. The suffix is automatically stripped to determine the entity property. For example, `headerImageUpload` maps to the `headerImage` property on the entity.

**Available methods:**

| Method | Metadata | Accepted Types | Description |
|---|---|---|---|
| `new()` | alt + title | `image/*` | Standard field with metadata |
| `withDescription()` | alt + title + description | `image/*` | Field with long description |
| `simple()` | none | `image/*` | Upload only, no metadata |
| `forAllFiles()` | none | `*/*` | Accepts all file types |
| `forVideo()` | alt + title + description | `video/*` | Video-specific field |

### MediaUploadType (Symfony Form)

When building standard Symfony forms (outside EasyAdmin), use `MediaUploadType` directly:

```php
use UbeeDev\AdminBundle\Form\Type\MediaUploadType;

$builder->add('headerImageUpload', MediaUploadType::class, [
    'media_property' => 'headerImage',     // Entity property (required)
    'media_context'  => 'articles',        // Storage context (required)
    'show_metadata'  => true,              // Show alt + title (default: true)
    'show_description' => false,           // Show description (default: false)
    'accept_types'   => 'image/*',         // Accepted MIME types (default: image/*)
]);
```

**Options:**

| Option | Type | Default | Description |
|---|---|---|---|
| `media_property` | `string` | - | Media property name on the entity (required) |
| `media_context` | `string` | `'default'` | Storage context (required) |
| `show_metadata` | `bool` | `true` | Display alt and title fields |
| `show_description` | `bool` | `false` | Display description field |
| `accept_types` | `string` | `'image/*'` | MIME types accepted by the browser |

The form type automatically handles:
- File upload via `MediaManager`
- Assigning the media to the parent entity through the matching setter
- Updating metadata on an existing media

### Custom Data Types

The bundle provides form types that integrate with `lib-bundle` Value Objects:

| Form Type | Transformed Object | Symfony Base |
|---|---|---|
| `CustomEmailType` | `Email` | `EmailType` |
| `CustomNameType` | `Name` | `TextType` |
| `CustomPhoneType` | `PhoneNumber` | `TelType` |
| `CustomUrlType` | `Url` | `UrlType` |

Each type includes a DataTransformer that automatically converts between the form value (string) and the corresponding Value Object.

**Usage example:**

```php
use UbeeDev\AdminBundle\Form\Type\CustomNameType;
use UbeeDev\AdminBundle\Form\Type\CustomEmailType;
use UbeeDev\AdminBundle\Form\Type\CustomPhoneType;

$builder
    ->add('firstName', CustomNameType::class)
    ->add('email', CustomEmailType::class)
    ->add('phone', CustomPhoneType::class);
```

---

## Twig Extensions

The bundle automatically registers Twig functions for working with media in your templates:

### `media_url(media)`

Returns the public URL of a media. Returns `null` if the media is private.

```twig
{% set url = media_url(article.headerImage) %}
{% if url %}
    <img src="{{ url }}" alt="{{ article.headerImage.alt }}">
{% endif %}
```

### `media_path(media)`

Returns the relative file path on disk.

```twig
{{ media_path(article.document) }}
```

### `format_file_size(bytes)`

Formats a byte size into a human-readable string (B, KB, MB, GB, TB).

```twig
{{ format_file_size(media.contentSize) }}
{# Outputs for example: "2.4 MB" #}
```

---

## Internationalization

The bundle is translated in **English** and **French**. Translation files use the `admin` domain.

The `AdminLocaleRedirectController` automatically detects the user's preferred browser language and redirects to the dashboard with the appropriate locale.

**Supported languages:** `en`, `fr`

To use translations in your templates:

```twig
{% trans_default_domain 'admin' %}

{{ 'admin.admin_user.page.index'|trans }}
```

To override a translation, create a file in your application:

```yaml
# translations/admin.en.yaml
admin:
    admin_user:
        page:
            index: "My custom title"
```

---

## Markdown Preview

The bundle provides an AJAX endpoint for previewing Markdown content as HTML.

**Endpoint:** `POST /admin/markdown/preview`

The Markdown content is parsed through `lib-bundle`'s `MarkdownParser` and returned as HTML.

A Markdown documentation page is also available at `/admin/markdown-documentation`.

---

## Architecture

```
src/
├── Controller/
│   ├── Admin/
│   │   ├── AdminUserCrudController.php   # Administrator CRUD
│   │   ├── MediaCrudController.php       # Media library CRUD
│   │   └── MediaHelperController.php     # Media access endpoint
│   ├── AdminLocaleRedirectController.php # Locale detection
│   ├── AdminLoginController.php          # Authentication
│   └── PreviewMarkdownController.php     # Markdown preview
├── Entity/
│   └── AdminUser.php                     # User MappedSuperclass
├── EventSubscriber/
│   └── MediaUploadSubscriber.php         # Form upload handling
├── Form/
│   ├── DataTransformer/                  # Value Object transformers
│   ├── Field/
│   │   └── MediaUploadField.php          # EasyAdmin helper
│   └── Type/                             # Custom form types
├── Repository/
│   └── AdminUserRepository.php
├── Twig/
│   └── MediaTwigExtension.php            # Twig functions (media_url, etc.)
└── UbeeDevAdminBundle.php                # Main bundle class

config/
├── services.yaml                         # Auto-wired services
└── routing/
    └── admin.yaml                        # YAML routes

templates/                                # Bundle Twig templates
translations/                             # EN/FR translations
assets/                                   # SCSS stylesheets
```

---

## License

MIT - (c) 2025 Ubee Dev
