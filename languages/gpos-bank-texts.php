<?php

/**
 * Frontend uygulaması için kelime ve cümle çevirileri.
 *
 * @package GurmeHub
 */

return array(
	'paratika'             => array(
		'description' => __('Institution/Company ID and user information are required to integrate your Paratika POS. You can access the Institution/Company ID information on the Paratika Pos Panel by following the steps: My Information -> Basic Information. If you have not created a user before, you need to create a user by following the Users -> Add User steps in the same panel.', 'gurmepos'),
	),
	'sekerbank'             => array(
		'description' => __('You can ensure integration by entering the API information sent to you via e-mail by Şekerbank through this panel.', 'gurmepos'),
	),
	'ozan'                 => array(
		'description' => __('The information required for the Ozan Virtual Pos integration will be obtained from the Ozan Virtual Pos Screen.', 'gurmepos'),
	),
	'sipay'                => array(
		'description' => __('You can get the necessary api information for the integration of your Sipay Virtual POS: You can log in to the Sipay Virtual POS Panel and get it from the Settings -> Integration & API page.', 'gurmepos'),
	),
	'mollie'                => array(
		'description' => __('You can access your Mollie API information via mollie dashboard > my account.', 'gurmepos'),
	),
	'iyzico'               => array(
		'description' => __('API and security key information is required for iyzico integration. You can access your API and security keys from the Settings->Company Settings section of your iyzico merchant panel. ', 'gurmepos'),
	),
	'iyzico-iframe'        => array(
		'description' => __('API and security key information is required for iyzico integration. You can access your API and security keys from the Settings->Company Settings section of your iyzico merchant panel. ', 'gurmepos'),
	),
	'pay-with-iyzico'      => array(
		'description' => __('API and security key information is required for iyzico integration. You can access your API and security keys from the Settings->Company Settings section of your iyzico merchant panel. ', 'gurmepos'),
	),
	'esnekpos'             => array(
		'description' => __('Required information for Esnekpos integration will be provided from the Esnekpos  user panel. ', 'gurmepos'),
	),
	'param'                => array(
		'description' => __('Param API information is required for Param POS integration. To access this information, you can access the necessary information from the page that opens after logging in to Param internet branch and clicking the "My Integration Information" tab under the ParamPos Menu. ', 'gurmepos'),
	),
	'paytr'                => array(
		// translators: %s Site url.
		'description' => sprintf(__('For PayTR integration, API information and notification URL definition is required.<br><br>You can access API information from the <strong>Integration Information</strong> section under the <strong>Support & Setup</strong> menu on your panel.<br><br>Notification url: <strong> %s/gpos-paytr-callback</strong>', 'gurmepos'), home_url()),
	),
	'paytr-iframe'         => array(
		// translators: %s Site url.
		'description' => sprintf(__('For PayTR integration, API information and notification URL definition is required.<br><br>You can access API information from the <strong>Integration Information</strong> section under the <strong>Support & Setup</strong> menu on your panel.<br><br>Notification url: <strong> %s/gpos-paytr-callback</strong>', 'gurmepos'), home_url()),
	),
	'akbank'               => array(
		'description' => __('The information required for Akbank Virtual Pos integration will be obtained from the Akbank Virtual Pos Screen.', 'gurmepos'),
	),
	'akode'                => array(
		'description' => __('The information required for AKÖde POS integration will be obtained from the AKÖde POS Screen.', 'gurmepos'),
	),
	'craftgate'            => array(
		'description' => __('API Integration Information is required for Craftgate integration. This information is located in the Craftgate control panel: Admin Menu -> Merchant Settings Tab -> API Access Information.', 'gurmepos'),
	),
	'denizbank'            => array(
		'description' => __('After logging into your Denizbank Virtual Pos panel, you need to create a new API user for integration information, if you have not created one before. After logging in, you can create an API user record by logging into the User Editing and Authorization page from the User Management System menu on the left. You can provide integration by filling in the API user information you have created in the relevant places.', 'gurmepos'),
	),
	'finansbank'           => array(
		'description' => __('For integration information after logging into your Finansbank panel: If you have not created one before, create an API user with the help of User Management from the Administration Menu. Fill in the API user information you created to the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'qnbfinansbank-payfor' => array(
		'description' => __('For integration information after logging into your Finansbank panel: If you have not created one before, create an API user with the help of User Management from the Administration Menu. Fill in the API user information you created to the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'garanti'              => array(
		'description' => __('Obtain the necessary information for the Garanti Pos integration from the Garanti Virtual Pos Screen.', 'gurmepos'),
	),
	'halkbank'             => array(
		'description' => __('After logging into your Halkbank panel, click on the management menu. If no user has been created before, we create an "API USER" from the User list section. We take the necessary information from the menus on the left and fill it in the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'halkbank_mkd'             => array(
		'description' => __('After logging into your Halkbank panel, click on the management menu. If no user has been created before, we create an "API USER" from the User list section. We take the necessary information from the menus on the left and fill it in the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'ingbank'              => array(
		'description' => __('The information required for ING Bank Pos integration will be obtained from the ING Bank Virtual Pos Screen.', 'gurmepos'),
	),
	'isbank'               => array(
		'description' => __('After logging into the İşbank virtual pos system, if no API user has been created before, an API user must be created by entering the "Add New User" section under the "Management" heading. Then, API user information should be filled in the relevant places.', 'gurmepos'),
	),
	'kuveytturk'           => array(
		'description' => __('Information required for Kuveyt Türk Pos integration will be obtained from the Kuveyt Türk Virtual Pos Screen.', 'gurmepos'),
	),
	'teb'                  => array(
		'description' => __('After logging into your TEB bank virtual pos panel, we click on the management menu. If no user has been created before, we create an "API USER" from the User list section. We fill in the necessary information in the relevant places in the POS Entegratör. ', 'gurmepos'),
	),
	'vakifbank'            => array(
		'description' => __('We click on the "CONTRACTED MERCHANT TRANSACTIONS" section under the "MANAGEMENT" tab from the Vakıfbank Virtual Pos panel. The page that opens contains the necessary information for our company. We fill this information in the relevant places in POS Entegratör.', 'gurmepos'),
	),
	'yapikredi'            => array(
		'description' => __('The information required for the Yapı Kredi Virtual POS integration will be obtained from the Yapı Kredi Virtual POS Screen.', 'gurmepos'),
	),
	'ziraat'               => array(
		'description' => __('After logging into Ziraat Bank Virtual Pos panel, if you have not created an API user before, first we click on the "Management" tab, we define a new user by selecting "Role" API User from the "Add New User" field. Then, we fill in the "User Name" and "Password" information you have defined in this field in the relevant fields in POS Entegratör.', 'gurmepos'),
	),
	'paidora'              => array(
		'description' => __('API Integration Information is required for Paidora integration. This information: Paidora user panel: Information menu -> API Integration Information is located under the heading.', 'gurmepos'),
	),
	'lidio'                => array(
		'description' => __('API values generated by Lidio, which gives you access to all service methods. This values is produced by Lidio only and transmitted to you.', 'gurmepos'),
	),
	'united-payment'       => array(
		'description' => __('API values generated by United Payment, which gives you access to all service methods. This values is produced by United Payment only and transmitted to you.', 'gurmepos'),
	),
	'dummy-payment'        => array(
		'description' => __('Dummy Payment is a fake payment service created to show how payment processes are carried out without API information of payment institutions.', 'gurmepos'),
	),
	'paybull'              => array(
		'description' => __('API values generated by Pay Bull, which gives you access to all service methods. This values is produced by Pay Bull only and transmitted to you.', 'gurmepos'),
	),
	'paynkolay'            => array(
		'description' => __('API values generated by Pay N Kolay, which gives you access to all service methods. This values is produced by Pay N Kolay only and transmitted to you.', 'gurmepos'),
	),
	'paycell'            => array(
		'description' => __('API values generated by Paycell provide access to all service methods. These values are exclusively produced by Paycell and securely transmitted to you', 'gurmepos'),
	),
	'wyld'                 => array(
		'description' => __('The information required for the WYLD integration will be obtained from the WYLD screen.', 'gurmepos'),
	),
	'qnbpay'               => array(
		'description' => __('You can access API information from the Integration & API information page in the Settings menu in your QNBpay merchant panel.', 'gurmepos'),
	),
	'garanti-pay'          => array(
		'description' => __('You can learn your GarantiPAY integration information by sending an e-mail to <strong>eticaretdestek@garantibbva.com.tr</strong>.', 'gurmepos'),
	),
	'isbank-girogate'      => array(
		'description' => __('After logging into the İşbank virtual pos system, if no API user has been created before, an API user must be created by entering the "Add New User" section under the "Management" heading. Then, API user information should be filled in the relevant places.', 'gurmepos'),
	),
	'weepay'               => array(
		'description' => __('API values generated by Weepay, which gives you access to all service methods. This values is produced by Weepay only and transmitted to you.', 'gurmepos'),
	),
	'worldpay'             => array(
		'description'      => __('The information required for the Yapı Kredi Virtual POS integration will be obtained from the Yapı Kredi Virtual POS Screen.', 'gurmepos'),
		'installment_desc' => __('Enter your installment rates. If left blank, the installment option will be disabled.', 'gurmepos'),
	),
	'vepara'               => array(
		'description' => __('You can get the necessary api information for the integration of your Vepara Virtual POS: You can log in to the Vepara Virtual POS Panel and get it from the Settings -> Integration & API page.', 'gurmepos'),
	),
	'moka'               => array(
		'description' => __('You can obtain your API information by viewing your dealer information from your Moka admin panel..', 'gurmepos'),
	),
	'shopier'              => array(
		'description' => sprintf(
			// translators: %s Site url.
			__('API Integration Knowledge is required for Shopier integration. This information: Shopier user panel: You can add the site you will use the payment to in the Integrations->Module Management section, then enter the API information on the left in the Module Settings tab, and you must define <strong>%s/gpos-shopier-callback</strong> as the return address.', 'gurmepos'),
			home_url()
		),
	),
	'erpapay'               => array(
		'description' => __('You can view your Erpapay API information from your profile via your seller panel.', 'gurmepos'),
	),

	'papara'           => array(
		'description' => __('For integration information after logging into your Papara panel: If you have not created one before, create an API user with the help of User Management from the Administration Menu. Fill in the API user information you created to the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'papara-checkout'           => array(
		'description' => __('For integration information after logging into your Papara panel: If you have not created one before, create an API user with the help of User Management from the Administration Menu. Fill in the API user information you created to the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'hepsipay'           => array(
		'description' => __('Once your contracts for Hepsipay API information are completed, your sales representative will return the integration API information to you.', 'gurmepos'),
	),
	'vakifbank-katilim'           => array(
		'description' => __('You can get your API information from Vakıfbank by contacting sanalposdestek@vakifkatilim.com.tr', 'gurmepos'),
	),
	'ziraatpay'           => array(
		'description' => __('You need to log in to the ZiraatPay panel with the username and password given to you by the bank, create an API user and fill in the necessary fields in our plugin.', 'gurmepos'),
	),
	'akbank-json'           => array(
		'description' => __('You can access all information about Akbank integration at sanalpos.akbank.com. All the information that needs to be filled in on the left side is included in your company information.', 'gurmepos'),
	),
	'albaraka'           => array(
		'description' => __('Albaraka first sends you TEST API information via e-mail. You should enter this information while TEST MODE is open and try a transaction with the test card sent to you. If the transaction is completely successful, you can request LIVE API information from your bank and close the test mode. Note: TEST 3D Key (ENCKey) information: 10,10,10,10,10,10,10,10', 'gurmepos'),
	),
	'qnbfinansbank-payfor-v2'           => array(
		'description' => __('For integration information after logging into your Finansbank panel: If you have not created one before, create an API user with the help of User Management from the Administration Menu. Fill in the API user information you created to the relevant places in the POS Entegratör.', 'gurmepos'),
	),
	'ziraat-katilim'           => array(
		'description' => __('You can complete the integration by entering the API information sent to you by e-mail from your bank. Note: The MRBID (Corporate Code) value must be "12".', 'gurmepos'),
	),
	'isyerimpos'           => array(
		'description' => __('You can access all necessary API information from the İşyerimPOS Virtual POS panel.', 'gurmepos'),
	),
	'rubikpara'           => array(
		'description' => __('You can access all necessary API information from the RubikPara Virtual POS panel.', 'gurmepos'),
	),
	'Setcard'           => array(
		'description' => __('You can access all necessary API information from the Setcard Virtual POS panel.', 'gurmepos'),
	),
	'vakif-katilim'           => array(
		'description' => __('You can access all necessary API information from the Vakıf Katılım Virtual POS panel.', 'gurmepos'),
	),
	'tami'           => array(
		'description' => __('Portal.tami.com.tr adresinden tami için gerekli tüm API bilgilerine erişebilirsiniz. Edindiğiniz bilgileri sol taraftaki alanlara girerek entegrasyonu tamamlayabilirsiniz.', 'gurmepos'),
	),
);
