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
    
[![Screenshot-from-2023-03-27-17-32-20.png](https://i.postimg.cc/nr6dW3RB/Screenshot-from-2023-03-27-17-32-20.png)](https://postimg.cc/4HQb7QTx)


#### CMS Page Settings
    `Content -> Pages -> Edit -> Sitemap -> Show in Sitemap`.
    
[![Screenshot-from-2023-03-27-17-34-32.png](https://i.postimg.cc/7LVL9csG/Screenshot-from-2023-03-27-17-34-32.png)](https://postimg.cc/zLy5Xd18)    


#### Required Steps to exclude CMS Pages from Sitemap

- Set `Exclude CMS Pages from Sitemap` configuration field value to "Yes"
- Set `Show in Sitemap` CMS page(s) value to "No"

- ### Ability to exclude Category Pages from Sitemap

#### Configurations 
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude Category Pages from Sitemap`.

[![Screenshot-from-2023-03-27-17-32-20.png](https://i.postimg.cc/nr6dW3RB/Screenshot-from-2023-03-27-17-32-20.png)](https://postimg.cc/4HQb7QTx)

#### Category Attribute Settings
    `Catalog -> Categories -> Sitemap -> Show in Sitemap`.

[![Screenshot-from-2023-03-27-17-33-48.png](https://i.postimg.cc/mkqkMHdm/Screenshot-from-2023-03-27-17-33-48.png)](https://postimg.cc/BPTJfXfF)

#### Required Steps to exclude Category Pages from Sitemap

- Set `Exclude Category Pages from Sitemap` configuration field value to "Yes"
- Set `Show in Sitemap` Category attribute value(s) to "No"

- ### Ability to exclude Product Pages from Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude Product Pages from Sitemap`.

[![Screenshot-from-2023-03-27-17-32-20.png](https://i.postimg.cc/nr6dW3RB/Screenshot-from-2023-03-27-17-32-20.png)](https://postimg.cc/4HQb7QTx)

#### Product attribute Settings
    `Catalog -> Products -> Edit -> Search Engine Optimization  -> Show in Sitemap`.

[![Screenshot-from-2023-03-27-17-32-53.png](https://i.postimg.cc/RVVLT5gf/Screenshot-from-2023-03-27-17-32-53.png)](https://postimg.cc/4nqcJMkN)


#### Required Steps to exclude Product Pages from Sitemap

- Set `Exclude Product Pages from Sitemap` configuration field value to "Yes"
- Set `Show in Sitemap` Product attribute value(s) to "No"


- ### Ability to exclude Product Images from Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Exclude Product Images from Sitemap`.

[![Screenshot-from-2023-03-27-17-32-20.png](https://i.postimg.cc/nr6dW3RB/Screenshot-from-2023-03-27-17-32-20.png)](https://postimg.cc/4HQb7QTx)

#### Required Steps to exclude Product Images from Sitemap

- Set `Exclude Product Images from Sitemap` configuration field value to "Yes"


- ### Include custom PWA Pages to Sitemap

#### Configurations
    `Stores -> Configuration -> Aligent -> Sitemap -> Include PWA Pages to Sitemap`.

    `Stores -> Configuration -> Aligent -> Sitemap -> Sitemap Base Url`.

    `Stores -> Configuration -> Aligent -> Sitemap -> PWA Pages Url Key`.
    
[![Screenshot-from-2023-03-27-17-32-20.png](https://i.postimg.cc/nr6dW3RB/Screenshot-from-2023-03-27-17-32-20.png)](https://postimg.cc/4HQb7QTx)    

#### Required Steps to include custom PWA Pages to Sitemap

- Set `Include PWA Pages to Sitemap` configuration field value to "Yes"
- Define url key's for PWA pages using `PWA Pages Url Key` configuration table
- Define pwa site url for `Sitemap Base Url` configuration.
