# Smart Holistic+ Google Ad Manager Setup Tool
Automatically setup and update your Line Items on GAM for **Smart Holistic+**

## Overview
When setting up Smart Holistic+, your ad ops team would have to create hundreds of line items in GAM.

This tool automates setup for GAM.

## Getting Started

### Creating Google Credentials
You will need credentials to access your GAM account programmatically. This summarizes steps from [DFP docs](https://developers.google.com/doubleclick-publishers/docs/authentication) and the GAM PHP library [auth guide](https://github.com/googleads/googleads-php-lib).
1. If you haven't yet, sign up for a [GAM account](https://www.doubleclickbygoogle.com/solutions/revenue-management/dfp/).
2. Create Google developer credentials
   * Go to the [Google Developers Console Credentials page](https://console.developers.google.com/apis/credentials).
   * On the **Credentials** page, select **Create credentials**, then select **Service account key**.
   * Select **New service account**, and select JSON key type. You can leave the role blank.
   * Click **Create** to download a file containing a `.json` private key.
3. Enable API access to DFP
   * Sign into your [DFP account](https://www.google.com/dfp/). You must have admin rights.
   * In the **Admin** section, select **Global settings**
   * Ensure that **API access** is enabled.
   * Click the **Add a service account user** button.
     * Use the service account email for the Google developer credentials you created above.
     * Set the role to "Trafficker".
     * Click **Save**.

### Setting Up
1. Clone this repository.
2. Include the library via Composer:
`$ composer require googleads/googleads-php-lib
3. Rename key
   * Rename the Google credentials key you previously downloaded (`[something].json`) to `googleServiceAccount.json` and move it to the project root folder
4. Make a copy of [adsapi_php.ini](https://github.com/googleads/googleads-php-lib/blob/master/examples/AdManager/adsapi_php.ini) and save it into the project root folder.
5. In `adsapi_php.ini`, set the required fields:
   * `application_name` is the name of the application you used to get your Google developer credentials
   * `network_code` is your GAM network number; e.g., for `https://www.google.com/dfp/12398712#delivery`, the network code is `12398712`.
   * `jsonKeyFilePath`is the path to your JSON key file
   * `scopes` is "https://www.googleapis.com/auth/dfp"
   * `impersonatedEmail` is the email account of the user you want to impersonate as, if any (something like user@app.iam.gserviceaccount.com)

### Verifying Setup
Let's try it out! From the top level directory, run

`php script/connexionTest.php`

and you should whether the connexion is OK or not

## Creating Line Items

Modify the following settings in script/smartGamSetup.php

* Currency is the AdServer Currency (USD, EUR...)
* NetworkId according to your networkId

Then, from the root of the repository, run:

`php script/smartGamSetup.php`

You should be all set! Review your order, line items, and creatives to make sure they are correct. Then, approve the order in GAM.

