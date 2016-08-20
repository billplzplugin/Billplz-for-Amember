# Billplz-for-Amember
Accept payment using Billplz

# Features
1. Using Billplz API V3
2. Production Mode / Sandbox Mode
3. Auto Submit Ready

# Installation
1. Download and extract to your installation directory
2. Enable Billplz at Configuration
3. Configure Billplz API Key & Collection ID
   * Input *1* for production mode or;
   * Input *2* for sandbox mode
4. Input *FPX* or *PayPal* for Billplz Auto Submit Feature (*Optional*)
5. Save. Your aMember are Billplz ready!

# Clone Billplz Payment Gateway
1. Clone FILE of /amember/application/default/plugins/payment/billplz.php to /amember/application/default/plugins/payment/billplz1.php
2. Edit /amember/application/default/plugins/payment/billplz1.php and change these lines: 

 class Am_Paysystem_Billplz extends Am_Paysystem_Abstract
to 
 class Am_Paysystem_Billplz1 extends Am_Paysystem_Abstract

    protected $defaultTitle = 'Billplz';
to
    protected $defaultTitle = 'Billplz 1';

then you will see billplz1 payment plugin in amember CP -> Setup -> Plugins and will be able to enable it.

# Support

Email: sales@wanzul-hosting.com
Whatsapp: 014-5356443
Facebook: www.facebook.com/billplzplugin

Please consider a donation to developer
www.wanzul.net/donate

# Hosting

Get Cheap & Affordable Web Hosting at www.wanzul-hosting.com
- Low as RM40/year
- 1GB Storage
- 10GB Bandwidth
- Unlimited Features
- Litespeed Webserver
