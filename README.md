# Magento 2 Price From

Simple extension that will add a new hidden attribute to products called `price_from` 
that will be populated automatically on save for configurable products with the lowest 
price available.

This attribute will then be available in the REST API under `custom_attributes`.

## Console Command

This extension works on save of a configurable product but you are liekly to already
have configurable products and so this extension includes the following command
which will run through all configurable products setting the price from field.

    php bin/magento fa:price-from:update