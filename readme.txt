=== WooCommerce Price History & Sale Compliance ===
Contributors: theweblab
Tags: omnibus, compliance, price history, eu directive, sale compliance
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WooCommerce addon that tracks product price history and displays the lowest price in the last 30 days on product pages during a sale to comply with EU Omnibus Directive.

== Description ==

This plugin ensures your WooCommerce store complies with consumer protection laws like the EU's Omnibus Directive by automatically tracking product price history. It automatically records price changes and displays the lowest price in the last 30 days on the product page when an item is on sale.

**Key Features:**

* **Automated Price Tracking** - Automatically records every price change for simple and variable products
* **30-Day Lowest Price Display** - Shows the lowest price in the last 30 days on sale products, as required by EU law
* **Price History Chart** - Optionally displays a clean, professional line chart of past prices for transparency
* **Customizable Messaging** - Adjust the compliance message text and tracking period from the settings
* **Admin Reports** - View a complete log of all recorded price changes in the WordPress admin area
* **Variable Product Support** - Properly handles variations with separate price tracking for each variation
* **HPOS Compatible** - Fully compatible with WooCommerce High Performance Order Storage

**Legal Compliance:**

This plugin is designed to help comply with the EU Omnibus Directive (2019/2161), specifically Article 6a which requires displaying the lowest price in the 30 days prior to a price reduction. The directive affects all online stores selling to EU customers.

**Perfect For:**

* WooCommerce stores selling to EU customers (legally required)
* Any store wanting to build customer trust through price transparency
* Agencies managing multiple client WooCommerce stores
* Stores looking for a professional compliance solution

**How It Works:**

1. Install and activate the plugin
2. Configure settings under WooCommerce > Settings > Price History
3. The plugin automatically starts tracking price changes when you update products
4. During sales, customers see "Lowest price in the last 30 days: €X" message
5. Optional price charts show price history for enhanced transparency

== Installation ==

= Automatic Installation =

1. Go to your WordPress admin area and navigate to 'Plugins > Add New'
2. Search for 'WooCommerce Price History Compliance'
3. Click 'Install Now' and then 'Activate'
4. Configure the settings under 'WooCommerce > Settings > Price History'

= Manual Installation =

1. Download the plugin zip file
2. Go to 'Plugins > Add New' in your WordPress admin
3. Click 'Upload Plugin', choose the zip file, and click 'Install Now'
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Configure the settings under 'WooCommerce > Settings > Price History'

= After Installation =

1. Navigate to WooCommerce > Settings > Price History
2. Configure your compliance message and tracking period
3. Enable or disable the price history chart as desired
4. The plugin will automatically start tracking price changes on your next product update

== Frequently Asked Questions ==

= Is this plugin required for EU stores? =

Yes, if you sell to EU customers and offer sales/discounts, the EU Omnibus Directive legally requires displaying the lowest price in the 30 days prior to the discount.

= Does this work with variable products? =

Yes, the plugin properly tracks price history for each product variation separately. When customers select a variation, they see that specific variation's price history.

= Will this slow down my store? =

No. Price tracking only happens when you update products (not on every page load). The compliance message uses cached data and charts load asynchronously.

= Can I customize the compliance message? =

Yes, you can customize the message text and tracking period in WooCommerce > Settings > Price History. This allows for localization to different languages and markets.

= Does it work with WooCommerce HPOS? =

Yes, the plugin is fully compatible with WooCommerce High Performance Order Storage (HPOS) and declares this compatibility properly.

= What happens to my data if I deactivate the plugin? =

Price history data remains in your database. If you reactivate the plugin, all history is preserved. The plugin includes options for data management in the admin area.

= Can I export compliance data for audits? =

Yes, the admin reports section allows you to view and search all price changes, which is useful for compliance audits and record-keeping.

= Does this work with my theme? =

Yes, the plugin uses standard WooCommerce hooks and is designed to work with any properly coded WooCommerce-compatible theme.

== Screenshots ==

1. Plugin settings page under WooCommerce Settings
2. Compliance message display on product page during sale
3. Price history chart showing price trends
4. Admin reports showing complete price change history
5. Variable product with variation-specific price history

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic price tracking for simple and variable products
* EU Omnibus Directive compliance messaging
* Optional price history charts
* Admin reports and settings
* HPOS compatibility
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of the WooCommerce Price History & Sale Compliance plugin.

== Additional Information ==

**Minimum Requirements:**

* WordPress 5.8 or higher
* WooCommerce 6.0 or higher  
* PHP 7.4 or higher
* MySQL 5.6 or higher

**Supported Languages:**

The plugin is translation-ready and includes proper text domains for easy localization. Sample compliance messages are provided for major EU languages.

**Support:**

For technical support, please use the WordPress.org support forums. For feature requests and bug reports, please provide detailed information including your WordPress version, WooCommerce version, and active theme.

**Legal Disclaimer:**

This plugin provides technical tools to help with EU Omnibus Directive compliance but is not legal advice. Store owners should verify compliance requirements with their legal advisors for their specific jurisdiction and business model.