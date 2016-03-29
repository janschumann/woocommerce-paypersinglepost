# WooCommerce Pay Per Single Post

This WordPress plugin extends the WooCommerce Pay Per Post plugin, to be able to purchase a single post.

## Installation

* Download and install [WooCommerce Pay Per Post](http://wordpress.emoxie.com/woocommerce-pay-per-post/)
* Download this plugin and unpack it into the WodPress plugin directory
* Configure WooCommerce Pay Per Post http://exapmle.com/wp-admin/options-general.php?page=wcppp-plugin-options_options

## Configure Products

To make a product work for purchasing single posts, an attribute `post_id` has to be added.

* Go to the product edit page
* Go to the "attributes" tab
* Add a new attribute `post_id` with the content `post_id`

## Configure Posts

Thankfully the Pay Per Post plugin allows for more than one product to be configured per post. To do that

* Go to the Post edit page
* Go to the WooCommerce Pay Per Post section
* Select the products that need to be purchased in order to view the content (e.g. one for single posts and one for a subscription)

**NOTE** If you have use multiple products, you will need to use the ```[products ids='{{product_id}}']``` tag in the [Pay Per Post plugin settings](http://exapmle.com/wp-admin/options-general.php?page=wcppp-plugin-options_options), which is not documented!