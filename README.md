# Digital Marketplace DB design

The Digital Marketplace is designed as a online exchange platform for used items.\
User input data is stored in a mySQL database, split in several tables.

## Overview

The **digital_marketplace** database of the platform stores information about registered users, item ads and user orders. The database is separated in 4 tables:


### 1. Users
Collect information about registered users:

> 1. **ID** - generated automatically
> 2. **email** - required for account creation
> 3. **password** - required for account creation
> 4. **name** - required for account creation
> 5. **surname** - required for account creation
> 6. **address**
> 7. **postcode**
> 8. **phone**
> 9. **user ip** - used to track blocked users
> 10. **login attempts** - count and block users after 3 unsuccessful attempts
> 11. **status** - active or blocked
> 12. **image** - store profile image path

### 2. User ads
Store information about user item ads:

> 1. **ID** - generated automatically
> 2. **title** - required for ad creation
> 3. **category ID** - required for ad creation, matches category ID from the category table in DB
> 4. **user ID** - matches creator user ID from the users table in DB
> 5. **publish date** - if ad status is published, records the date/time
> 6. **condition** - item condition used/new
> 7. **description**
> 8. **price** - required for ad creation
> 9. **status** - ads could be saved as drafts or be published and made visible on the platform
> 10. **image** - store product image path

### 3. Category
Store a list of item categories (not editable by users):

> 1. **ID** - generated automatically
> 2. **category name**
> 3. **color** - color associated with each category

### 4. Orders
Store information about each order:

> 1. **ID** - generated automatically
> 2. **user ID** - matches buyer user ID from the users table in DB
> 3. **ad ID** - matches item ad ID from the user_ads table in DB
> 4. **purchase date** - records the date/time the order was made
