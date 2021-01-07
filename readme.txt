=== Woo Crowdfunding ===
Contributors:
Donate link: https://paypal.me/ivoslavik
Tags: woo commerce, crowdfunding, funding, sponsorship
Requires at least: 4.7
Tested up to: 5.6
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Woo Crowdfunding turns WooCommerce into a crowdfunding platform.

== Description ==

This plugin was inspired by [WooSponsorship](https://github.com/chrislema/WooSponsorship).

It turns WooCommerce e-shop into a crowdfunding platform adding a new type of product called 'Crowdfunding Project'.

**Features**

* CF project contains contribution levels with or without reward.
* Contribution levels are shown in a separate tab in CF project detail.
* Contribution level can have stock quantity set.
* Contributors can allow their names and contributed amounts to be publicly visible (in CF project detail separate tab) during checkout.
* Contribution is included in the overall sum when order is completed.
* Standard products and CF projects can't be mixed in the cart.
* Contributions for multiple CF projects can't be mixed in the cart.
* When CF project is over and goal is not met, all related orders are turned to 'Refunded' status automatically.
* It is possible to filter orders by CF project on orders overview page.

**Hooks**

* Cancelled CF projects are hidden by default. Use `woo-cf-cancelled-project-visibility` filter to show them.

== Installation ==

1. Upload `woo-crowdfunding` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create a new product and set 'Crowdfunding Project' for product type

== Donation ==

[https://paypal.me/ivoslavik](https://paypal.me/ivoslavik)