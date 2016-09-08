# mage2-ordered-assets
Order assets (read: css tags) explicitly with an order attribute

Magento2 has no way to order *assets* out of the box. This extension allows you to specify an *order* attribute in *css* tags in layout XML files and layout updates in the admin UI.

## Installation
### Composer
`composer require quickshiftin/assetorderer`

### Manual
Download the repository and add it in your Magento2 installation under **app/code/Quickshiftin/Assetorderer**

### Magento commands
Once you've installed the code via composer or download, you need to run the Magento DI compiler:

* `bin/magento module:enable Quickshiftin_Assetorderer`
* `bin/magento setup:upgrade`
* `rm -rf var/cache var/di var/generation var/page_cache && bin/magento setup:di:compile`

## Usage
Suppose you want to add a custom CSS file, *css/home.css*, on your homepage. Ordinarilly you would enter this in the layout update editor 
```
<head>
<css src="css/home.css"/>
</head>
```

However, Magento most likely will place the generated *link* tag **before** the base CSS file, thus not honoring the cascade. With the extension installed you can enter the *css* tag with an arbitrary *order* attribute like so
```
<head>
<css src="css/home.css" order="100" />
</head>
```

Any tags without an explicit order will come as they appear normally (effectively treated like they have an order of 1).
