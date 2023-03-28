# Magento 2 Sitemap Module
This module is used for magento 2 sitemap xml enhancements. This module is mainly contains following features.

- Ability to exclude CMS Pages from Sitemap XML
- Ability to exclude Category Pages from Sitemap XML
- Ability to exclude Product Pages from Sitemap XML
- Ability to exclude Product Images from Sitemap XML
- Include custom PWA Pages to Sitemap XML

## Installation
To install via composer, simply run:

```bash
composer require aligent/magento2-sitemap-extension
```

## Configuration and Settings

- ### Ability to exclude CMS Pages from Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude CMS Pages from Sitemap`.

#### CMS Page Settings
    `Content -> Pages -> Edit -> Sitemap -> Show in Sitemap`.

#### Required Steps to exclude CMS Pages from Sitemap

- Set `Exclude CMS Pages from Sitemap` configuration field value to "Yes"
- Set `Show in Sitemap` CMS page(s) value to "No"

- ### Ability to exclude Category Pages from Sitemap

#### Configurations 
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude Category Pages from Sitemap`.

#### Category Attribute Settings
    `Catalog -> Categories -> Sitemap -> Show in Sitemap`.

#### Required Steps to exclude Category Pages from Sitemap

- Set `Exclude Category Pages from Sitemap` configuration field value to "Yes"
- Set `Show in Sitemap` Category attribute value(s) to "No"

- ### Ability to exclude Product Pages from Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude Product Pages from Sitemap`.

#### Product attribute Settings
    `Catalog -> Products -> Edit -> Search Engine Optimization  -> Show in Sitemap`.

#### Required Steps to exclude Product Pages from Sitemap

- Set `Exclude Product Pages from Sitemap` configuration field value to "Yes"
- Set `Show in Sitemap` Product attribute value(s) to "No"


- ### Ability to exclude Product Images from Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude Product Images from Sitemap`.

#### Required Steps to exclude Product Images from Sitemap

- Set `Exclude Product Images from Sitemap` configuration field value to "Yes"


- ### Include custom PWA Pages to Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Include PWA Pages to Sitemap`.

    `Stores -> Configuration -> Aligent -> Sitemap -> Sitemap Base Url`.

    `Stores -> Configuration -> Aligent -> Sitemap -> PWA Pages Url Key`.


#### Required Steps to include custom PWA Pages to Sitemap

- Set `Include PWA Pages to Sitemap` configuration field value to "Yes"
- Define url key's for PWA pages using `PWA Pages Url Key` configuration table
- Define pwa site url for `Sitemap Base Url` configuration.
