<h1>
	<img src="./docs/assets/domos.svg" height="35" style="margin-right: 10px;"  alt="logo"/>
	<br />
	<span>domos/schema</span>
	<h6>© 2024 domos GmbH</h6>
</h1>

<br/>

> [!WARNING]
> Not ready for public use yet. API surface is subject to change.

<br>

`domos/schema` is the reference implementation of the domos real estate data schema written in PHP.

```bash
composer install domos/schema
```

# Schema Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Accessing Estate Data in WordPress](#accessing-estate-data-in-wordpress)
   - [Finding an Estate](#finding-an-estate)
   - [Accessing Estate Data in WordPress Templates](#accessing-estate-data-in-wordpress-templates)
   - [Finding Multiple Estates](#finding-multiple-estates)
   - [Important Notes for Website Building](#important-notes-for-website-building)
   - [Estate Management Operations (Internal Use)](#estate-management-operations-internal-use)
3. [Core Models](#core-models)
   - [Estate](#estate)
   - [Building](#building)
   - [Rentable](#rentable)
4. [Location Models](#location-models)
   - [Address](#address)
   - [Coordinates](#coordinates)
   - [Location](#location)
5. [Media Models](#media-models)
   - [Image](#image)
   - [Media](#media)
   - [Video](#video)
   - [Scan](#scan)
   - [CameraFeed](#camerafeed)
6. [Financial Models](#financial-models)
   - [Money](#money)
   - [Price](#price)
7. [Web Expose Models](#web-expose-models)
   - [WebExpose](#webexpose)
   - [Block](#block)
8. [Utility Models](#utility-models)
   - [Contact](#contact)
   - [Certifications](#certifications)

## Introduction

This document provides a comprehensive overview of the real estate data schema implemented in PHP. The schema is designed to represent various aspects of real estate properties, including buildings, rentable spaces, locations, media assets, and web exposures. It uses modern PHP features such as typed properties, enums, and interfaces to create a robust and flexible system for managing real estate data.

## Accessing Estate Data in WordPress

The `EstatePost` class provides a bridge between the Estate data schema and WordPress, allowing you to retrieve and display estate information stored as WordPress posts. This section explains how to use the `EstatePost` class to access estate data within a WordPress environment, focusing on tasks relevant to building websites.

### Finding an Estate

To retrieve an estate by its external ID:

```php
$externalId = 'your-external-id';
$estatePost = EstatePost::find($externalId);

if ($estatePost !== null) {
    $estate = $estatePost->data; // This is an instance of SchemaImmo\Estate
    echo "Estate Name: " . $estate->name;
    echo "Estate Address: " . $estate->address->street . " " . $estate->address->number;
} else {
    echo "Estate not found";
}
```

### Accessing Estate Data in WordPress Templates

When working with estate data in WordPress templates, you can use the `EstatePost::fromPost()` method to get the estate data from the current post:

```php
global $post;

if ($post->post_type === EstatePost::POST_TYPE) {
    $estatePost = EstatePost::fromPost($post);
    $estate = $estatePost->data;

    // Now you can access all the estate data
    echo "<h1>{$estate->name}</h1>";
    echo "<p>Address: {$estate->address->street} {$estate->address->number}, {$estate->address->city}</p>";

    if ($estate->media->thumbnail) {
        echo "<img src='{$estate->media->thumbnail->src}' alt='{$estate->media->thumbnail->alt}'>";
    }

    // Display features
    if (!empty($estate->features)) {
        echo "<h2>Features:</h2><ul>";
        foreach ($estate->features as $feature => $value) {
            echo "<li>{$feature}: {$value}</li>";
        }
        echo "</ul>";
    }

    // Display rentable spaces
    if (!empty($estate->buildings)) {
        foreach ($estate->buildings as $building) {
            echo "<h2>Building: {$building->name}</h2>";
            foreach ($building->rentables as $rentable) {
                echo "<h3>Rentable Space: {$rentable->name}</h3>";
                echo "<p>Area: {$rentable->area} sqm</p>";
                echo "<p>Price: {$rentable->price->base->amount} {$rentable->price->base->currency->value}</p>";
            }
        }
    }
}
```

This example demonstrates how to access and display various aspects of the estate data within a WordPress template.

### Finding Multiple Estates

To retrieve multiple estates, you can use WordPress's `WP_Query` class in combination with `EstatePost::fromPost()`:

```php
$args = array(
    'post_type' => EstatePost::POST_TYPE,
    'posts_per_page' => 10,
    // Add any other query arguments as needed
);

$query = new WP_Query($args);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $estatePost = EstatePost::fromPost($query->post);
        $estate = $estatePost->data;

        // Display estate information
        echo "<h2>{$estate->name}</h2>";
        echo "<p>{$estate->address->city}, {$estate->address->country}</p>";
        // Add more fields as needed
    }
    wp_reset_postdata();
} else {
    echo "No estates found";
}
```

This example shows how to query for multiple estates and display basic information for each.

### Estate Management Operations (Internal Use)

The following operations are primarily for internal use and data management:

- **Creating a new estate**: `EstatePost::create($externalId, $estateData)`
- **Updating an existing estate**: `EstatePost::update($externalId, $updatedEstateData)`
- **Deleting an estate**: `EstatePost::delete($externalId)`
- **Finding unneeded estates**: `EstatePost::findUnneeded($activeIds)`

These methods should be used with caution and typically within administrative interfaces or background processes, rather than in public-facing website code.

By using the `EstatePost` class, you can seamlessly integrate the real estate data schema with WordPress, allowing for easy retrieval and display of complex estate information on your WordPress site.

## Core Models

### Estate

The `Estate` class is the central model representing a real estate property.

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier |
| slug | string | URL-friendly identifier |
| name | string | Name of the estate |
| address | [Address](#address) | Physical address of the estate |
| features | array | List of features and amenities |
| buildings | array | List of [Building](#building) objects within the estate |
| texts | [Texts](#texts) | Descriptive content about the estate |
| media | [Media](#media) | Images, videos, and 3D scans related to the estate |
| location | [Location](#location) | Geographical location and nearby places |
| certifications | [Certifications](#certifications) | Environmental and quality certifications |
| social | [Social](#social) | Social media and contact information |
| expose | ?[WebExpose](#webexpose) | Web-based property exposure data |

Usage:
```php
$estate = new Estate(
    id: '123',
    slug: 'sample-estate',
    name: 'Sample Estate',
    address: new Address(
        street: 'Main St',
        number: '123',
        postal_code: '12345',
        city: 'Sample City',
        country: 'US'
    )
);
$arrayRepresentation = $estate->toArray();
```

### Building

The `Building` class represents a specific building within an estate.

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier |
| name | ?string | Name of the building |
| address | ?[Address](#address) | Physical address of the building |
| area | ?float | Total area of the building in square meters |
| media | [Media](#media) | Images, videos, and 3D scans related to the building |
| features | array | List of features and amenities specific to the building |
| rentables | array | List of [Rentable](#rentable) spaces within the building |

Usage:
```php
$building = new Building(
    id: 'B1',
    name: 'Building 1',
    area: 1000.0,
    media: new Media()
);
$arrayRepresentation = $building->toArray();
```

### Rentable

The `Rentable` class represents a space that can be rented or sold within a building.

| Property | Type | Description |
|----------|------|-------------|
| id | string | Unique identifier |
| name | ?string | Name of the rentable space |
| area | ?float | Area of the space in square meters |
| description | ?string | Detailed description of the space |
| transaction_type | [TransactionType](#transactiontype) | Type of transaction (Rent or Sale) |
| price | ?[Price](#price) | Pricing information for the space |
| spaces | array | List of [Space](#space) objects within the rentable area |
| features | array | List of features and amenities specific to the rentable space |
| media | [Rentable\Media](#rentablemedia) | Images, videos, and 3D scans related to the rentable space |

Usage:
```php
$rentable = new Rentable(
    id: 'R1',
    name: 'Office Space 1',
    area: 100.0,
    transaction_type: TransactionType::Rent,
    price: new Price(
        base: new Money(1000.0, Currency::Euro)
    )
);
$arrayRepresentation = $rentable->toArray();
```

## Location Models

### Address

The `Address` class represents a physical address.

| Property | Type                         | Description |
|----------|------------------------------|-------------|
| street | string                       | Street name |
| number | ?string                      | Street number |
| postal_code | ?string                      | Postal code |
| city | ?string                      | City name |
| country | ?string                      | Country code |
| coordinates | ?[Coordinates](#coordinates) | Geographical coordinates of the address |
| label | ?string                      | Custom label for the address |

Usage:
```php
$address = new Address(
    street: 'Main St',
    number: '123',
    postal_code: '12345',
    city: 'Sample City',
    country: 'US'
);
$arrayRepresentation = $address->toArray();
```

### Coordinates

The `Coordinates` class represents geographic coordinates.

| Property | Type | Description |
|----------|------|-------------|
| latitude | float | Latitude value |
| longitude | float | Longitude value |

Usage:
```php
$coordinates = new Coordinates(
    latitude: 40.7128,
    longitude: -74.0060
);
$arrayRepresentation = $coordinates->toArray();
```

### Location

The `Location` class represents the location of an estate, including nearby places.

| Property | Type | Description |
|----------|------|-------------|
| places | array | List of nearby [Place](#place) objects |

Usage:
```php
$location = new Location();
$location->places[] = new Place(
    type: Place\Type::from('restaurant'),
    name: 'Sample Restaurant',
    coordinates: new Coordinates(40.7128, -74.0060)
);
$arrayRepresentation = $location->toArray();
```

## Media Models

### Image

The `Image` class represents an image asset.

| Property | Type | Description |
|----------|------|-------------|
| src | string | Source URL of the image |
| alt | ?string | Alternative text for accessibility |

Usage:
```php
$image = new Image();
$image->src = 'https://example.com/image.jpg';
$image->alt = 'Sample Image';
$arrayRepresentation = $image->toArray();
```

### Media

The `Media` class represents a collection of media assets for an estate.

| Property | Type | Description |
|----------|------|-------------|
| thumbnail | ?[Image](#image) | Thumbnail image for the estate |
| gallery | array | Collection of [Image](#image) objects |
| logo | ?[Image](#image) | Logo image for the estate |
| videos | array | Collection of [Video](#video) objects |
| scans | array | Collection of [Scan](#scan) objects |

Usage:
```php
$media = new Media();
$media->thumbnail = new Image();
$media->thumbnail->src = 'https://example.com/thumbnail.jpg';
$arrayRepresentation = $media->toArray();
```

### Video

The `Video` class represents a video asset.

| Property | Type | Description |
|----------|------|-------------|
| id | ?string | Unique identifier for the video |
| type | [Video\Type](#videotype) | Type of video (e.g., Embed, Direct) |
| thumbnail_url | ?string | URL of the video thumbnail image |

Usage:
```php
$video = new Video(
    type: Video\Type::Embed,
    thumbnail_url: 'https://example.com/video_thumbnail.jpg'
);
$arrayRepresentation = $video->toArray();
```

### Scan

The `Scan` class represents a 3D scan or virtual tour.

| Property | Type | Description |
|----------|------|-------------|
| id | ?string | Unique identifier for the scan |
| type | [Scan\Type](#scantype) | Type of scan (e.g., Embed) |
| provider | ?string | Name of the scan provider |

Usage:
```php
$scan = new Scan(
    type: Scan\Type::Embed,
    provider: 'Matterport'
);
$arrayRepresentation = $scan->toArray();
```

### CameraFeed

The `CameraFeed` class represents a live camera feed.

| Property | Type | Description |
|----------|------|-------------|
| id | ?string | Unique identifier for the camera feed |
| type | [CameraFeed\Type](#camerafeedtype) | Type of camera feed (e.g., Embed) |
| provider | ?string | Name of the camera feed provider |

Usage:
```php
$cameraFeed = new CameraFeed(
    type: CameraFeed\Type::Embed,
    provider: 'Sample Provider'
);
$arrayRepresentation = $cameraFeed->toArray();
```

## Financial Models

### Money

The `Money` class represents a monetary value.

| Property | Type | Description |
|----------|------|-------------|
| amount | float | Numeric amount |
| currency | [Currency](#currency) | Currency of the amount |

Usage:
```php
$money = new Money(
    amount: 1000.0,
    currency: Currency::Euro
);
$arrayRepresentation = $money->toArray();
```

### Price

The `Price` class represents the price of a rentable space.

| Property | Type | Description |
|----------|------|-------------|
| base | [Money](#money) | Base price |
| extra_costs | ?[Money](#money) | Additional costs |

Usage:
```php
$price = new Price(
    base: new Money(1000.0, Currency::Euro),
    extra_costs: new Money(100.0, Currency::Euro)
);
$arrayRepresentation = $price->toArray();
```

## Web Expose Models

### WebExpose

The `WebExpose` class represents the structure for web-based property exposure.

| Property | Type | Description |
|----------|------|-------------|
| sidebar_features | array | Features to display in the sidebar |
| pool_features | array | Features to display in the pool section |
| blocks | array | List of content [Block](#block) objects |

Usage:
```php
$webExpose = new WebExpose();
$webExpose->sidebar_features = ['feature1', 'feature2'];
$webExpose->blocks[] = new TextBlock('Sample text content');
$arrayRepresentation = $webExpose->toArray();
```

### Block

The `Block` class is an abstract base class for various types of content blocks in the web expose.

| Property | Type | Description |
|----------|------|-------------|
| id | ?string | Unique identifier for the block |
| type | [BlockType](#blocktype) | Type of the content block |

Usage (example with TextBlock):
```php
$block = new TextBlock(
    text: 'Sample text content',
    id: 'block1'
);
$arrayRepresentation = $block->toArray();
```

## Utility Models

### Contact

The `Contact` class represents contact information.

| Property | Type | Description |
|----------|------|-------------|
| name | string | Name of the contact |
| role | ?string | Role or position of the contact |
| email | ?string | Email address |
| phone | ?string | Phone number |
| mobile | ?string | Mobile number |
| avatar | ?[Image](#image) | Avatar image of the contact |
| address | ?[Address](#address) | Physical address of the contact |

Usage:
```php
$contact = new Contact(
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+1234567890'
);
$arrayRepresentation = $contact->toArray();
```

### Certifications

The `Certifications` class represents various certifications for an estate.

| Property | Type | Description |
|----------|------|-------------|
| dgnb | ?[DGNBCertification](#dgnbcertification) | DGNB (German Sustainable Building Council) certification level |
| co2_neutral | ?bool | Indicates if the estate is CO2 neutral |

Usage:
```php
$certifications = new Certifications();
$certifications->dgnb = DGNBCertification::Gold;
$certifications->co2_neutral = true;
$arrayRepresentation = $certifications->toArray();
```
