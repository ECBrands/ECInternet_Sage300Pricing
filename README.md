# Magento2 Module ECInternet_Sage300Pricing
``ecinternet_sage300pricing - 1.1.7.0``

- [Requirements](#requirements-header)
- [Overview](#overview-header)
- [Installation](#installation-header)
- [Configuration](#configuration-header)
- [Design Modifications](#design-modifications-header)
- [Specifications](#specifications-header)
- [Attributes](#attributes-header)
- [Notes](#notes-header)
- [Version History](#version-history-header)

## Requirements

## Overview

## Installation Instructions
- Extract the zip to your Magento 2 root directory to create the following folder structure: `app/code/ECInternet/Sage300Pricing`
- Run `php -f bin/magento module:enable ECInternet_Sage300Pricing`
- Run `php -f bin/magento setup:upgrade`
- Run `php -f bin/magento setup:di:compile`
- Flush the Magento cache
- Done

## Configuration
- GENERAL
  - Enable Module
  - Enable Debug Logging
- DISPLAY
  - Admin Pricing Table Title
- GROUP PRICES
  - Guest Price Group
- TIER PRICES
  - Display Tier Prices on Frontend

## Design Modifications
### Adminhtml
- Product form
  - Add custom modal under `Price` attribute

### Frontend
- Layout `catalog_product_view`
  - Block `product.info.main`
    - Add new block for tier price display
  - Block `product.price.final`
    - Remove existing block which shows possibly cached price
  - Block `product.info.price`
    - Add new block for product price display

## Specifications
### ICCUPR - Contract Pricing
 - `PRICETYPE`
   - 1	=	Customer Type
   - 2	=	Discount Percentage
   - 3	=	Discount Amount
   - 4	=	Cost Plus a Percentage
   - 5	=	Cost Plus Fixed Amount
   - 6	=	Fixed Price

### ICPRIC - Item Pricing
- `PRICETYPE`
  - 1	=	Discount
  - 2	=	Markup on Markup Cost
  - 3	=	Markup on Standard Cost
  - 4	=	Markup on Most Recent Cost
  - 5	=	Markup on Average Cost
  - 6	=	Markup on Last Unit Cost
  - 7	=	Markup on Landed


- `PRICEFMT`
  - 1	=	Percentage
  - 2	=	Amount


- `PRICEBASE`
  - 1	=	Customer Type
  - 2	=	Volume Discounts


- `PRICEBY`
  - 1	=	Quantity
  - 2	=	Weight

### ICPRICP - Item Pricing Details
- `DPRICETYPE`
  - 1	=	Base Price Quantity
  - 2	=	Sale Price Quantity
  - 3	=	Base Price Weight
  - 4	=	Sale Price Weight
  - 5	=	Base Price Using Cost
  - 6	=	Sale Price Using Cost

## Attributes
- `Customer`
  - Customer Type (`customer_type`)
  - Currency Code (`currency_code`)

## Features

## Notes

## Known Issues

## Version History
