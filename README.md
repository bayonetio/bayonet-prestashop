# Bayonet Anti-Fraud Module for PrestaShop

This module will make you able to use the technology of Bayonet in your PrestaShop shop to prevent online fraud. In this way, your shop will obtain a win/win performance for you and your customers; which means that you will know when a suspicious order by a suspicious customer is trying to be processed, and at the same time your shop will gain a reputation of being a secure place to buy.

The module requires **Prestashop 1.6** and some pretty easy configurations in order to function properly.

*Read this in other languages: [Español](README.es.md).*

## Table of Contents
  - [Bayonet Anti-Fraud Installation](#bayonet-anti-fraud-installation)
  - [Bayonet Anti-Fraud Configuration](#bayonet-anti-fraud-configuration)
    - [History Backfill](#history-backfill)
  - [Bayonet Anti-Fraud Management](#bayonet-anti-fraud-management)
    - [History Backfill](#history-backfill)
    - [Bayonet Anti-Fraud Result in Order Details](#bayonet-anti-fraud-result-in-order-details)
    - [Bayonet Anti-Fraud Blocklist](#bayonet-anti-fraud-blocklist)
    - [Bayonet Anti-Fraud Tab in Back Office](#bayonet-anti-fraud-tab-in-back-office)

## Bayonet Anti-Fraud Installation

The next steps will guide you through Bayonet’s module installation.\
Things you need for this task:
- Your PrestaShop store credentials
- Bayonet’s module zip file

1. Log into the back office of your store.

<p align="center">
  <img src="https://i.imgur.com/vW270uq.png">
</p>

2. Navigate to the modules section using the dashboard’s sidebar, hovering over “Modules and Services” and then selecting “Modules and Services”.

<p align="center">
  <img src="https://i.imgur.com/F3SaUMB.png">
</p>

3. Press the “Add a new module” button, located at the upper right of the “List of modules” page, a panel will be shown in the page which will allow you to upload the module.

<p align="center">
  <img src="https://i.imgur.com/rMY8Hq2.png">
</p>

4. Press the “Choose a file” button to open the dialog box and then select the compressed file to upload, in this case, “bayonet.zip”.

<p align="center">
  <img src=https://i.imgur.com/OKQSeER.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/q4jaTfE.png">
</p>

5. Press the button “Upload this module” to upload Bayonet’s module. A confirmation message will be shown after the module is uploaded and the option to install it will become available.

<p align="center">
  <img src="https://i.imgur.com/DPXjM3p.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/fXBQDYt.png">
</p>

6. Press the “Install” button. A dialog box will appear showing the module’s information and will ask to confirm the installation, press “Proceed with the installation” to confirm.\
After the installation is completed, PrestaShop will display the configuration page of the module.

<p align="center">
  <img src="https://i.imgur.com/hvFofbt.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/xclBQWA.png">
</p>

## Bayonet Anti-Fraud Configuration

Here you will see how to configure the module and what is each configuration for. This task is required to make this module work properly.\
Things you need for this task:
- Bayonet’s API keys
- Device Fingerprinting API keys

<p align="center">
  <img src="https://i.imgur.com/WMDpggl.png">
</p>

The keys for both APIs are obtained in Bayonet’s console, to do that, you will need to log into [Bayonet’s console](https://bayonet.io/login) using your Bayonet’s credentials to get them or generate them if you haven’t done that. 
If you haven’t received your credentials yet, please send an email to contacto@bayonet.io with your information to provide you with them.

The steps to get your API keys are as follows:

1. Log into [Bayonet’s console](https://bayonet.io/login) using your Bayonet credentials.

<p align="center">
  <img src="https://i.imgur.com/9WAZxg4.png">
</p>

2. Once logged in, choose the “Developers” category.

<p align="center">
  <img src="https://i.imgur.com/KRQ2Jdy.png">
</p>

3. Select the “Setup” tab. In this tab you will be able to see everything related to your API keys.

<p align="center">
  <img src="https://i.imgur.com/cBlF5e3.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/uwzW8jA.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/l5OQj7O.png">
</p>

In this tab, you will be able to generate sandbox API keys for both Bayonet and the Device Fingerprint from the start, however, the generation of production keys will be enabled once you have added your billing details in the billing section.

Once you generate your keys, you will be able to use them in order to set up the module in its configuration page on PrestaShop.

***Make sure to keep these keys safe, do not include them in any email or share it with people outside your development team.
If you believe your key was compromised, do not hesitate in generate a new one.***

In the next image, you can see how the configuration page looks.

<p align="center">
  <img src="https://i.imgur.com/jU5UJpa.png">
</p>

__Live Mode__: this option will set Bayonet’s API mode. 
Selecting “No” will analyze every order in sandbox (test) mode, otherwise, the orders will be analyzed in live (production) mode.

__Bayonet API Sandbox Key__: this key is needed to use Bayonet’s module in sandbox mode.

__Bayonet API Live Key__: this key is needed to use Bayonet’s module in live mode.

__Device Fingerprint API Sandbox Key__: this key is needed to use the Device Fingerprint API in sandbox mode.

__Device Fingerprint API Live Key__: this key is needed to use the Device Fingerprint API in live mode.

### History Backfill
This section is where you will execute the process to analyze your existing orders with Bayonet, this will help the module to have a better understanding of your store and your customers. At first, this section will appear disabled, you will need to add your live API keys and save them in order to make this section available. Once you successfully save your API keys, this is how the backfill section will look.

<p align="center">
  <img src="https://i.imgur.com/aOgiaYA.png">
</p>

_IMPORTANT_\
The module will show an error if you try to save with empty/incorrect fields, please fill every one with the correct information in order to avoid any errors. Each of these errors will give you a different message depending on which field is trying to save something incorrect.

## Bayonet Anti-Fraud Management
### History Backfill
Once the module has been successfully installed and configured, the first step to take afterwards is to run the backfill process, this is very important to help the module know more about your store and your customers.
To do this, press the “INITIATE BACKFILL” in the module’s configuration page.

<p align="center">
  <img src="https://i.imgur.com/hUF6uWz.png">
</p>

This will initiate the backfill process and a progress bar will appear showing the current completion percentage.

<p align="center">
  <img src="https://i.imgur.com/jZZSlBl.png">
</p>

After the backfill process has been initiated, you can either wait for it to finish, or stop its execution pressing the “STOP BACKFILL” button below the progress bar.\
_*Note: if you close the page without stopping the process, it will keep running in the background._

Completing the backfill process will mean Bayonet’s module is ready to analyze each one of your new orders. The analysis process will be performed automatically by the module every time a new order is placed.

### Bayonet Anti-Fraud Result in Order Details
The analysis will evaluate the information regarding that specific order and customer, and it will give a decision which can be one of the following three; accept, review and decline.

After an order has been analyzed by Bayonet, you can check its result in the details of the order in the back office. 

<p align="center">
  <img src="https://i.imgur.com/wdexqAY.png">
</p>

This panel includes:
- **Decision**: this will tell you how you should act over that specific order.
	- ACCEPTED: the order did not trigger any rules to consider it as a potential fraud, you should not take any actions on this order.
	- REVIEW: the order is not as safe to give the decision to accept it, but is not as risky to decline it right away. In this case, you should decide whether you cancel the order or take no actions on it.
	- DECLINED: the order has a high risk of being a fraudulent transaction. You should cancel the order as soon as possible.
- **Bayonet Tracking ID**: a unique identifier generated by Bayonet for this transaction in the analysis process.
- **API Call Status**: includes the data received by the Bayonet API call, which help to know if any errors were present making it, the data is composed by a numeric code associated to a message.
- **Rules Triggered**: this will show the triggered rules to get the decision. It is possible to not trigger any rules.

The panel will show a warning message if the order was not processed by Bayonet or if it was part of the backfill process.

<p align="center">
  <img src="https://i.imgur.com/hmVurHN.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/RJqZXjE.png">
</p>

### Bayonet Anti-Fraud Blocklist
The Bayonet Anti-Fraud panel has two buttons, “Add Customer to Blacklist” and “Add Customer to Whitelist”. The function of the first one is to add the customer of the order that you are currently visualizing to the Bayonet whitelist, in this way, all their transactions will be automatically accepted. In the same way, the second button, will immediately decline all the customer’s transactions.

In the next image, you can see the way the panel looks for an order whose customer was previously added to the Bayonet blacklist.

<p align="center">
  <img src="https://i.imgur.com/3bahwcJ.png">
</p>

The decision will appear as “DECLINED”, and within the triggered rules when analyzing the order, there is “blocked_by_client”, which means that your store’s owner or admin added this transaction’s customer to the Bayonet blacklist. Likewise, you can see how the blacklist button change its legend to “Remove Customer from Blacklist” when the customer is already on the list.

### Bayonet Anti-Fraud Tab in Back Office
The module’s installation adds a new tab in the back office, this is located at the bottom of the sidebar, with the legend “Bayonet Anti-Fraud”. Selecting this tab, will show its content, which is a table with all orders of your store that have been processed by Bayonet, specifically by the consulting API.

<p align="center">
  <img src="https://i.imgur.com/xTCaaIN.png">
</p>

<p align="center">
  <img src="https://i.imgur.com/2iYyrZk.png">
</p>

Breaking down its content, at the top you have the column names.

<p align="center">
  <img src="https://i.imgur.com/gYHtSgh.png">
</p>

The columns of this table are:
- **ID**: the unique identifier for the Bayonet table in PrestaShop’s database (not confuse with the Bayonet Tracking ID).
- **Cart**: the cart ID of that specific order.
- **Order**: the ID of the order.
- **Bayonet Tracking ID**: the unique identifier generated by Bayonet for this order in the analysis process. 
- **Decision**: the decision given by the analysis process.
	- Accept
	- Review
	- Decline

Next, you have a filter area, where you can define a specific set of orders to display. For example, you can enter “ACCEPT” in the Decision filter, pressing the button “Search” will display only the orders have “ACCEPT” as decision. You can clear the filters by pressing the “Reset” button.

<p align="center">
  <img src="https://i.imgur.com/u5Td4An.png">
</p>

Besides filtering the data on the table, you can also order its rows by ID, Cart, Order or Decision. To do this, just click one of the two chevron arrows next to a column name, the downwards arrow will do a descending order, while the upwards arrow, will arrange the rows in an ascending order.

The table also has the option to visualize a specific order individually; by pressing the ID of an order, you will be automatically redirected to its details in the “Orders” section.

Finally, in the lower part of the table, you have a pagination feature, its behavior will be affected by the number of rows set to display per page. You can choose to display between 20, 50, 100, 300, 500 and 1000 rows per page, after modifying this value, the number of pages will change depending on how many entries are in your store’s Bayonet table.

<p align="center">
  <img src="https://i.imgur.com/s2VR1Qk.png">
</p>

For further reference, please check the [user's manual](bayonet-manual-EN.docx)
