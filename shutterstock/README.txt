=== Shutterstock ===
Contributors: shutterstockplugins
Tags: shutterstock, stock photography, images, editorial images, licensing, media library, stock
Requires at least: 5.5
Tested up to: 5.5
Stable tag: 1.1.0
Requires PHP: 7.1
License: MIT
License URI: http://opensource.org/licenses/mit-license.html

Insert Shutterstock's royalty-free content directly from the WordPress editor

== Description ==

The Shutterstock plugin for WordPress allows you to access our exceptional, royalty-free content directly from WordPress. You can search Shutterstock's library, put preview images on pages and posts, and license, download, and post images and editorial content without leaving the Wordpress page editor. By helping streamline your workflow at the point of production and publishing, you can get your message to market more efficiently and effectively than ever before.

The Shutterstock plugin takes the complexity out of creativity and saves time, whether you're creating a draft or publishing a full article.

**Search**
Choose content from Shutterstock's incredible collection.

**Preview**
Take your ideas for a test run and use our watermarked content on draft posts and pages.

**License**
Love how it looks? License the content and use it directly within your posts and pages.

This plugin uses the Shutterstock API. For more information, see [https://developers.shutterstock.com](https://developers.shutterstock.com).

== Screenshots ==

1. Search for images and insert them into pages and posts

== Prerequisites ==

To install the Shutterstock plugin for WordPress, you need an API application for the Shutterstock API. You can create an application at [https://www.shutterstock.com/account/developers/apps](https://www.shutterstock.com/account/developers/apps). 

The plugin works only with API applications that have referrer authentication enabled. In your application, you must specify the host names on which your WordPress site runs.

To add referrer authentication to your app:

1. Go to [https://shutterstock.com/account/developers/apps](https://shutterstock.com/account/developers/apps).
1. Create or edit an app you want to use with the plugin.
1. In the **Referrer** field, specify a comma-separated list of host names that the WordPress server runs on.
You can specify "localhost" as a referrer for local testing.
1. Make sure that each referrer host name is also listed in the **Callback URL** field.
1. Save the app and note the consumer key and consumer secret.

To set up the plugin in WordPress, you also need:

- WordPress version 5.5 or later
- An account on the WordPress server with administrator access
- A Shutterstock subscription that is enabled for API use; see [Subscriptions](https://api-reference.shutterstock.com/#subscriptions) in the Shutterstock API documentation.

== Installation ==

1. Log in to WordPress as a user with the Administrator role.
1. In the WordPress admin console, click **Plugins > Add New**.
1. In the Add Plugins window, search for the Shutterstock plugin, click **Install Now**, and then click **Activate**.

   As an alternative, you can download the latest release of the plugin from [https://wordpress.org/plugins/](https://wordpress.org/plugins/) and install it manually by going to the Add Plugins window, clicking **Upload Plugin**, selecting the plugin file, clicking **Install Now**, and then clicking **Activate Plugin**.

1. In the WordPress admin console, go to **Settings > Shutterstock**.
1. Put your API application's consumer key in the **API Key** field and its consumer secret in the **API Secret** field.
1. Click **Log in with Shutterstock** and log in with the user name and password of the Shutterstock account that you want to use to access the Shutterstock media library. Now the plugin has a token that it can use to access the library.

   ![Logging in to the API via the WordPress plugin](plugin-settings.png)

1. In the **Editorial Country** field, enter the three-character country code for editorial content, such as USA or DEU.
1. In the **User Settings** table, select which WordPress roles have access to search and license media.
1. Click **Save Changes**.

Now that you have the Shutterstock plugin installed and configured, you can use it to add Shutterstock media to your pages:

1. Open a post or page in the WordPress editor.
1. Add a Shutterstock block.

   ![Shutterstock block added to the page](browse_button.png)

1. In the new block, click **Browse**. 
1. In the popup window, search Shutterstock's library for media to add.
1. To try a piece of media in your page, click its **Insert Preview** button. WordPress adds a watermarked preview of the image to your page.

   ![Watermarked preview on the page](watermarked_preview.png)

1. To license the image and put the non-watermarked image on the page, click **License this image**, select your plan and size, and click **License**. The Shutterstock plugin downloads the media to the default media upload folder for WordPress. You can see the image in the WordPress Media tab.
1. In the block settings, update the alt text and dimensions of the image. Other block settings allow you to set HTML attributes including the title, anchor, and class.

   ![Watermarked preview on the page](inserted_image.png)

1. Publish the page as usual. The image appears on the page just like any other image that you add to a page.

== Changelog ==

= 1.1.0 =
Initial version
