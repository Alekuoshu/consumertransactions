<?php
/**
* 2007-2019 PrestaShop
*
*  @author    Farmalisto <alejandro.villegas@farmalisto.com.co>
*  @copyright 2007-2019 Farmalisto
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('_PS_VERSION_')) {
    exit;
}

const CONSUMERTRANSACTIONS_PATH_LOG = _PS_ROOT_DIR_ . "/modules/consumertransactions/log/";

class Consumertransactions extends Module
{
    protected $config_form = false;
    private $CONSUMERTRANSACTIONS_LIVE_MODE;
    private $CONSUMERTRANSACTIONS_ACCOUNT_EMAIL;
    private $CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD;

    public function __construct()
    {
        $this->name = 'consumertransactions';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Farmalisto';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Consumer Transactions');
        $this->description = $this->l('Module for send emails of consumer transactions to a client');

        $this->confirmUninstall = $this->l('Are you sure want uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->CONSUMERTRANSACTIONS_LIVE_MODE = Configuration::get('CONSUMERTRANSACTIONS_LIVE_MODE');
        $this->CONSUMERTRANSACTIONS_ACCOUNT_EMAIL = Configuration::get('CONSUMERTRANSACTIONS_ACCOUNT_EMAIL');
        $this->CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD = Configuration::get('CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD');

        if ($this->active && Configuration::get('consumertransactions') == '') {
            $this->warning = $this->l('You have to configure your module');
           }
         
           $this->errors = array();
           if ($this->CONSUMERTRANSACTIONS_LIVE_MODE == 1) {
               $this->emailTransactions();
           }
        // ?k=Alewushu
        // DGLC@nsumerT.2019!
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('CONSUMERTRANSACTIONS_LIVE_MODE', false);
        Configuration::updateValue('CONSUMERTRANSACTIONS_ACCOUNT_EMAIL', '');
        Configuration::updateValue('CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD', '');

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('CONSUMERTRANSACTIONS_LIVE_MODE');
        Configuration::deleteByName('CONSUMERTRANSACTIONS_ACCOUNT_EMAIL', '');
        Configuration::deleteByName('CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD', '');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitConsumertransactionsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitConsumertransactionsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'CONSUMERTRANSACTIONS_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'required' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter valid emails address separated by comma'),
                        'name' => 'CONSUMERTRANSACTIONS_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                        'desc' => $this->l('Key for value'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CONSUMERTRANSACTIONS_LIVE_MODE' => Configuration::get('CONSUMERTRANSACTIONS_LIVE_MODE', true),
            'CONSUMERTRANSACTIONS_ACCOUNT_EMAIL' => Configuration::get('CONSUMERTRANSACTIONS_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD' => Configuration::get('CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
  * Error log
  *
  * @param string $text text that will be saved in the file
  * @return void Error record in file "log_errors.log"
  */
  public static function logtxt($text = "")
  {

    if (file_exists(CONSUMERTRANSACTIONS_PATH_LOG)) {
        $fp = fopen(_PS_ROOT_DIR_ . "/modules/consumertransactions/log/log_errors.log", "a+");
        fwrite($fp, date('l jS \of F Y h:i:s A') . ", " . $text . "\r\n");
        fclose($fp);
        return true;
    } else {
        self::createPath(CONSUMERTRANSACTIONS_PATH_LOG);
    }
  }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayHome()
    {
        /* Place your code here. */
    }

    /**
     * This function is for create csv file to send emails transactions
     */
    public function emailTransactions()
    {
        // self::logtxt("probando log...");
        if ($this->CONSUMERTRANSACTIONS_LIVE_MODE == 1) {
            $key = $this->CONSUMERTRANSACTIONS_ACCOUNT_PASSWORD;
            
            if(Tools::getValue('k') == $key){
                
                var_dump('funcionando!');

                // obtenemos el lenguaje para saber el pais a enviar
                $languages = Language::getLanguages(true, $this->context->shop->id);
                $lang_code = $languages[0]['language_code'];
                if($lang_code == 'es-co') {
                    $country = 'Colombia';
                    $iso = 'co';
                }
                if($lang_code == 'es-mx') {
                    $country = 'México';
                    $iso = 'mx';
                }

                // evalua que el historico este vacio o no
                $db = Db::getInstance();
                $sql = 'SELECT * FROM '._DB_PREFIX_.'ct_transactions_history';
                $history = $db->getValue($sql);

                // history vacio
                if ($history == false) {

                    // obtenemos las ordenes para enviar el primer reporte
                    $sql = new DbQuery();
                    $sql->select('A.id_order, A.reference, A.id_customer, A.total_paid, A.date_add, A.payment, B.note, C.product_name, C.product_quantity, C.product_price, C.product_reference, C.product_quantity_discount');
                    $sql->from('orders', 'A');
                    $sql->innerJoin('customer', 'B', 'A.id_customer = B.id_customer');
                    $sql->innerJoin('order_detail', 'C', 'A.id_order = C.id_order');
                    $sql->where('A.current_state = 5');
                    $orderstoSend = Db::getInstance()->executeS($sql);

                    // echo "<pre>";
                    // var_dump($orderstoSend);
                    // echo "</pre>";

                    if (!empty($orderstoSend)){

                        var_dump('Nuevos registros...');

                        // si no existe se crea el directorio para guardar los reportes
                        if (!file_exists('DGL')) {
                            $dir = mkdir("DGL", 0777);
                        }
                        
                        // Se crea el archivo CSV
                        $archivo_csv = fopen('DGL/DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv', 'w');
                        if($archivo_csv)
                        {
                            self::logtxt("1-El archivo se creo exitoso!");
                            var_dump('1-El archivo se creo exitoso!');

                            fputs($archivo_csv, "Country__c,Contact_Num__c,Payment_Type__c,Comment__c,X1_Product__c,X1_Quantity__c,X1_Lot__c,X1_Price__c,X1_SKU__c,X1_IVA__c,X2_Product__c,X2_Quantity__c,X2_Lot__c,X2_Price__c,X2_SKU__c,X2_IVA__c,X3_Product__c,X3_Quantity__c,X3_Lot__c,X3_Price__c,X3_SKU__c,X3_IVA__c,X4_Product__c,X4_Quantity__c,X4_Lot__c,X4_Price__c,X4_SKU__c,X4_IVA__c,X5_Product__c,X5_Quantity__c,X5_Lot__c,X5_Price__c,X5_SKU__c,X5_IVA__c,X6_Product__c,X6_Quantity__c,X6_Lot__c,X6_Price__c,X6_SKU__c,X6_IVA__c,Delivery_Time__c,Transaction_Date__c,Comment_Alternative_Address__c,Comment_Other__c,Delivered__c,Vendor_Order_Id__c,Order_Value__c,Discount_Applied__c,Origin__c,Loyalty_Points__c".PHP_EOL);  

                            fclose($archivo_csv);

                            foreach ($orderstoSend as $key => $order) {
                                // creamos los datos a enviar
                                // formateo de fecha
                                $fechaHora = $order['date_add'];
                                $fechaHora2 = explode(' ', $fechaHora);
                                $fecha = $fechaHora2[0];
        
                                $data1 = array();
                                $data1[$key]['Country__c'] = $country;
                                $data1[$key]['Contact_Num__c'] = $order['note'];
                                $data1[$key]['Payment_Type__c'] = $order['payment'];
                                $data1[$key]['Comment__c'] = '';
                                $data1[$key]['X1_Product__c'] = $order['product_name'];
                                $data1[$key]['X1_Quantity__c'] = (int)$order['product_quantity'];
                                $data1[$key]['X1_Lot__c'] = '';
                                $data1[$key]['X1_Price__c'] = (float)$order['product_price'];
                                $data1[$key]['X1_SKU__c'] = $order['product_reference'];
                                $data1[$key]['X1_IVA__c'] = '';
                                $data1[$key]['X2_Product__c'] = '';
                                $data1[$key]['X2_Quantity__c'] = '';
                                $data1[$key]['X2_Lot__c'] = '';
                                $data1[$key]['X2_Price__c'] = '';
                                $data1[$key]['X2_SKU__c'] = '';
                                $data1[$key]['X2_IVA__c'] = '';
                                $data1[$key]['X3_Product__c'] = '';
                                $data1[$key]['X3_Quantity__c'] = '';
                                $data1[$key]['X3_Lot__c'] = '';
                                $data1[$key]['X3_Price__c'] = '';
                                $data1[$key]['X3_SKU__c'] = '';
                                $data1[$key]['X3_IVA__c'] = '';
                                $data1[$key]['X4_Product__c'] = '';
                                $data1[$key]['X4_Quantity__c'] = '';
                                $data1[$key]['X4_Lot__c'] = '';
                                $data1[$key]['X4_Price__c'] = '';
                                $data1[$key]['X4_SKU__c'] = '';
                                $data1[$key]['X4_IVA__c'] = '';
                                $data1[$key]['X5_Product__c'] = '';
                                $data1[$key]['X5_Quantity__c'] = '';
                                $data1[$key]['X5_Lot__c'] = '';
                                $data1[$key]['X5_Price__c'] = '';
                                $data1[$key]['X5_SKU__c'] = '';
                                $data1[$key]['X5_IVA__c'] = '';
                                $data1[$key]['X6_Product__c'] = '';
                                $data1[$key]['X6_Quantity__c'] = '';
                                $data1[$key]['X6_Lot__c'] = '';
                                $data1[$key]['X6_Price__c'] = '';
                                $data1[$key]['X6_SKU__c'] = '';
                                $data1[$key]['X6_IVA__c'] = '';
                                $data1[$key]['Delivery_Time__c'] = '';
                                $data1[$key]['Transaction_Date__c'] = $fecha;
                                $data1[$key]['Comment_Alternative_Address__c'] = '';
                                $data1[$key]['Comment_Other__c'] = '';
                                $data1[$key]['Delivered__c'] = 1;
                                $data1[$key]['Vendor_Order_Id__c'] = $order['reference'];
                                $data1[$key]['Order_Value__c'] = (float)$order['total_paid'];
                                $data1[$key]['Discount_Applied__c'] = (float)$order['product_quantity_discount'];
                                $data1[$key]['Origin__c'] = 'eCommerce';
                                $data1[$key]['Loyalty_Points__c'] = 0;
    
                                // echo "<pre>";
                                // var_dump($data1);
                                // echo "</pre>";
                                
                                // ingresando la data al archivo csv
                                $values = implode(',', $data1[$key]);

                                // echo "<pre>";
                                // var_dump($values);
                                // echo "</pre>";
                                
                                $archivo_csv = fopen('DGL/DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv', 'a');
                                if($archivo_csv)
                                {
                                    $updateFile = fputs($archivo_csv, $values.PHP_EOL);
                                    fclose($archivo_csv);
                                    if ($updateFile == true) {
                                        self::logtxt("2-El archivo se actualizo!");
                                        var_dump('2-El archivo se actualizo!');
                                    }else {
                                        self::logtxt("2-El archivo no se pudo actualizar!");
                                        var_dump('2-El archivo no se pudo actualizar!');
                                    }
                                }else{
                                    self::logtxt("2-El archivo no existe");
                                    var_dump('2-El archivo no existe');
                                }
        
        
                            } // fin foreach orderstoSend

                            // Envio del archivo por correo
                            $email = Mail::Send_dgl(
                                (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                                'dgl_consumer_trans', // email template file to be use
                                'DGL Consumer Transaction '.date("Y-m-d"), // email subject
                                array(
                                    '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                                    '{message}' => 'Hi, attached is the csv file...' // email content
                                ),
                                $this->CONSUMERTRANSACTIONS_ACCOUNT_EMAIL, // receiver email address 
                                NULL, //receiver name
                                NULL, //from email address
                                'Abbott Nutricionales',  //from name
                                array(
                                    'content' => file_get_contents(_PS_ROOT_DIR_.'/DGL/DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv'),
                                    'mime' => 'text/csv',
                                    'name' => 'DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv'
                                ),
                                NULL,  //SMTP mode
                                NULL,  //Mails directory
                                NULL,  //Die after error?
                                NULL,  //ID Shop
                                'alejandro.villegas@farmalisto.com.co',  //BCC
                                NULL  //Reply to
                            );

                            if ($email != false) {
                                self::logtxt("Email enviado exitoso!!");
                                var_dump('Email enviado exitoso!!');

                                foreach ($orderstoSend as $key => $order) {

                                    // formateo de fecha
                                    $fechaHora = $order['date_add'];
                                    $fechaHora2 = explode(' ', $fechaHora);
                                    $fecha = $fechaHora2[0];

                                    // Insertamos data en ct_transactions_history tabla
                                    $result =  Db::getInstance()->insert('ct_transactions_history', array(
                                        'Country__c' => $country,
                                        'Contact_Num__c' => $order['note'],
                                        'Payment_Type__c' => $order['payment'],
                                        'X1_Product__c' => $order['product_name'],
                                        'X1_Quantity__c' => $order['product_quantity'],
                                        'X1_Price__c' => (float)$order['product_price'],
                                        'X1_SKU__c' => $order['product_reference'],
                                        'Transaction_Date__c' => $fecha,
                                        'Delivered__c' => 1,
                                        'Vendor_Order_Id__c' => $order['reference'],
                                        'Order_Value__c' => (float)$order['total_paid'],
                                        'Discount_Applied__c' => (float)$order['product_quantity_discount'],
                                        'Origin__c' => 'eCommerce',
                                        'Loyalty_Points__c' => 0,
                                        'created_date' => date("Y-m-d H:i:s"),
                                    ));
                                    $error = Db::getInstance()->getMsgError();
            
                                    if ($result == true){
                                        self::logtxt("Registros guardados al history con exito");
                                        var_dump("Registros guardados al history con exito");
                                    }else {
                                        if ($error != ''){
                                            self::logtxt($error);
                                        }
                                        self::logtxt("Hubo un error al intentar guardar en el history");
                                        var_dump("1-Hubo un error al intentar guardar en el history");
                                    }
            
                                    // var_dump($result);

                                } // fin foreach

                            }else {
                                self::logtxt("Error, email no pudo ser enviado!!");
                                var_dump('Error, email no pudo ser enviado!!');
                            }

                        }else{
                            self::logtxt("1-El archivo no existe o no se pudo crear");
                            var_dump('1-El archivo no existe o no se pudo crear');
                        }


                    }else {
                        self::logtxt("No hay registros para enviar...");
                        var_dump('No hay registros para enviar...');
                    }


                } else {
                    # si hay registros en el historico

                    // obtenemos las nuevas ordenes a enviar que no esten en el historico
                    $sql = new DbQuery();
                    $sql->select('A.id_order, A.reference, A.id_customer, A.total_paid, A.date_add, A.payment, B.note, C.product_name, C.product_quantity, C.product_price, C.product_reference, C.product_quantity_discount');
                    $sql->from('orders', 'A');
                    $sql->innerJoin('customer', 'B', 'A.id_customer = B.id_customer');
                    $sql->innerJoin('order_detail', 'C', 'A.id_order = C.id_order');
                    $sql->where('A.current_state = 5 AND NOT EXISTS (SELECT Vendor_Order_Id__c FROM ps_ct_transactions_history WHERE Vendor_Order_Id__c = A.reference)');
                    $orderstoSend = Db::getInstance()->executeS($sql);
                    // $error = Db::getInstance()->getMsgError();

                    // echo "<pre>";
                    // var_dump($orderstoSend);
                    // echo "</pre>";

                    if (!empty($orderstoSend)){

                        var_dump('Nuevos registros...');

                        // si no existe se crea el directorio para guardar los reportes
                        if (!file_exists('DGL')) {
                            $dir = mkdir("DGL", 0777);
                        }
                        
                        // Se crea el archivo CSV
                        $archivo_csv = fopen('DGL/DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv', 'w');
                        if($archivo_csv)
                        {
                            self::logtxt("1-El archivo se creo exitoso!");
                            var_dump('1-El archivo se creo exitoso!');

                            fputs($archivo_csv, "Country__c,Contact_Num__c,Payment_Type__c,Comment__c,X1_Product__c,X1_Quantity__c,X1_Lot__c,X1_Price__c,X1_SKU__c,X1_IVA__c,X2_Product__c,X2_Quantity__c,X2_Lot__c,X2_Price__c,X2_SKU__c,X2_IVA__c,X3_Product__c,X3_Quantity__c,X3_Lot__c,X3_Price__c,X3_SKU__c,X3_IVA__c,X4_Product__c,X4_Quantity__c,X4_Lot__c,X4_Price__c,X4_SKU__c,X4_IVA__c,X5_Product__c,X5_Quantity__c,X5_Lot__c,X5_Price__c,X5_SKU__c,X5_IVA__c,X6_Product__c,X6_Quantity__c,X6_Lot__c,X6_Price__c,X6_SKU__c,X6_IVA__c,Delivery_Time__c,Transaction_Date__c,Comment_Alternative_Address__c,Comment_Other__c,Delivered__c,Vendor_Order_Id__c,Order_Value__c,Discount_Applied__c,Origin__c,Loyalty_Points__c".PHP_EOL);  

                            fclose($archivo_csv);

                            foreach ($orderstoSend as $key => $order) {
                                // creamos los datos a enviar
                                // formateo de fecha
                                $fechaHora = $order['date_add'];
                                $fechaHora2 = explode(' ', $fechaHora);
                                $fecha = $fechaHora2[0];
        
                                $data2 = array();
                                $data2[$key]['Country__c'] = $country;
                                $data2[$key]['Contact_Num__c'] = $order['note'];
                                $data2[$key]['Payment_Type__c'] = $order['payment'];
                                $data2[$key]['Comment__c'] = '';
                                $data2[$key]['X1_Product__c'] = $order['product_name'];
                                $data2[$key]['X1_Quantity__c'] = (int)$order['product_quantity'];
                                $data2[$key]['X1_Lot__c'] = '';
                                $data2[$key]['X1_Price__c'] = (float)$order['product_price'];
                                $data2[$key]['X1_SKU__c'] = $order['product_reference'];
                                $data2[$key]['X1_IVA__c'] = '';
                                $data2[$key]['X2_Product__c'] = '';
                                $data2[$key]['X2_Quantity__c'] = '';
                                $data2[$key]['X2_Lot__c'] = '';
                                $data2[$key]['X2_Price__c'] = '';
                                $data2[$key]['X2_SKU__c'] = '';
                                $data2[$key]['X2_IVA__c'] = '';
                                $data2[$key]['X3_Product__c'] = '';
                                $data2[$key]['X3_Quantity__c'] = '';
                                $data2[$key]['X3_Lot__c'] = '';
                                $data2[$key]['X3_Price__c'] = '';
                                $data2[$key]['X3_SKU__c'] = '';
                                $data2[$key]['X3_IVA__c'] = '';
                                $data2[$key]['X4_Product__c'] = '';
                                $data2[$key]['X4_Quantity__c'] = '';
                                $data2[$key]['X4_Lot__c'] = '';
                                $data2[$key]['X4_Price__c'] = '';
                                $data2[$key]['X4_SKU__c'] = '';
                                $data2[$key]['X4_IVA__c'] = '';
                                $data2[$key]['X5_Product__c'] = '';
                                $data2[$key]['X5_Quantity__c'] = '';
                                $data2[$key]['X5_Lot__c'] = '';
                                $data2[$key]['X5_Price__c'] = '';
                                $data2[$key]['X5_SKU__c'] = '';
                                $data2[$key]['X5_IVA__c'] = '';
                                $data2[$key]['X6_Product__c'] = '';
                                $data2[$key]['X6_Quantity__c'] = '';
                                $data2[$key]['X6_Lot__c'] = '';
                                $data2[$key]['X6_Price__c'] = '';
                                $data2[$key]['X6_SKU__c'] = '';
                                $data2[$key]['X6_IVA__c'] = '';
                                $data2[$key]['Delivery_Time__c'] = '';
                                $data2[$key]['Transaction_Date__c'] = $fecha;
                                $data2[$key]['Comment_Alternative_Address__c'] = '';
                                $data2[$key]['Comment_Other__c'] = '';
                                $data2[$key]['Delivered__c'] = 1;
                                $data2[$key]['Vendor_Order_Id__c'] = $order['reference'];
                                $data2[$key]['Order_Value__c'] = (float)$order['total_paid'];
                                $data2[$key]['Discount_Applied__c'] = (float)$order['product_quantity_discount'];
                                $data2[$key]['Origin__c'] = 'eCommerce';
                                $data2[$key]['Loyalty_Points__c'] = 0;
    
                                // echo "<pre>";
                                // var_dump($data2);
                                // echo "</pre>";
                                
                                // ingresando la data al archivo csv
                                $values = implode(',', $data2[$key]);

                                // echo "<pre>";
                                // var_dump($values);
                                // echo "</pre>";

                                $archivo_csv = fopen('DGL/DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv', 'a');
                                if($archivo_csv)
                                {
                                    $updateFile = fputs($archivo_csv, $values.PHP_EOL);
                                    fclose($archivo_csv);
                                    if ($updateFile == true) {
                                        self::logtxt("2-El archivo se actualizo!");
                                        var_dump('2-El archivo se actualizo!');
                                    }else {
                                        self::logtxt("2-El archivo no se pudo actualizar!");
                                        var_dump('2-El archivo no se pudo actualizar!');
                                    }
                                }else{
                                    self::logtxt("2-El archivo no existe");
                                    var_dump('2-El archivo no existe');
                                }
        
                            } // fin foreach orderstoSend

                            // Envio del archivo por correo
                            $email = Mail::Send_dgl(
                                (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
                                'dgl_consumer_trans', // email template file to be use
                                'DGL Consumer Transaction '.date("Y-m-d"), // email subject
                                array(
                                    '{email}' => Configuration::get('PS_SHOP_EMAIL'), // sender email address
                                    '{message}' => 'Hi, attached is the csv file...' // email content
                                ),
                                $this->CONSUMERTRANSACTIONS_ACCOUNT_EMAIL, // receiver email address 
                                NULL, //receiver name
                                NULL, //from email address
                                'Abbott Nutricionales',  //from name
                                array(
                                    'content' => file_get_contents(_PS_ROOT_DIR_.'/DGL/DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv'),
                                    'mime' => 'text/csv',
                                    'name' => 'DGL_Consumer_Transaction_'.$iso.'_'.date("Y-m-d").'.csv'
                                ),
                                NULL,  //SMTP mode
                                NULL,  //Mails directory
                                NULL,  //Die after error?
                                NULL,  //ID Shop
                                'alejandro.villegas@farmalisto.com.co',  //BCC
                                NULL  //Reply to
                            );

                            if ($email != false) {
                                self::logtxt("Email enviado exitoso!!");
                                var_dump('Email enviado exitoso!!');

                                foreach ($orderstoSend as $key => $order) {

                                    // formateo de fecha
                                    $fechaHora = $order['date_add'];
                                    $fechaHora2 = explode(' ', $fechaHora);
                                    $fecha = $fechaHora2[0];

                                    // Insertamos data en ct_transactions_history tabla
                                    $result =  Db::getInstance()->insert('ct_transactions_history', array(
                                        'Country__c' => $country,
                                        'Contact_Num__c' => $order['note'],
                                        'Payment_Type__c' => $order['payment'],
                                        'X1_Product__c' => $order['product_name'],
                                        'X1_Quantity__c' => $order['product_quantity'],
                                        'X1_Price__c' => (float)$order['product_price'],
                                        'X1_SKU__c' => $order['product_reference'],
                                        'Transaction_Date__c' => $fecha,
                                        'Delivered__c' => 1,
                                        'Vendor_Order_Id__c' => $order['reference'],
                                        'Order_Value__c' => (float)$order['total_paid'],
                                        'Discount_Applied__c' => (float)$order['product_quantity_discount'],
                                        'Origin__c' => 'eCommerce',
                                        'Loyalty_Points__c' => 0,
                                        'created_date' => date("Y-m-d H:i:s"),
                                    ));
                                    $error = Db::getInstance()->getMsgError();
            
                                    if ($result == true){
                                        self::logtxt("Registros guardados al history con exito");
                                        var_dump("Registros guardados al history con exito");
                                    }else {
                                        if ($error != ''){
                                            self::logtxt($error);
                                        }
                                        self::logtxt("Hubo un error al intentar guardar en el history");
                                        var_dump("1-Hubo un error al intentar guardar en el history");
                                    }
            
                                    // var_dump($result);

                                } // fin foreach

                            }else {
                                self::logtxt("Error, email no pudo ser enviado!!");
                                var_dump('Error, email no pudo ser enviado!!');
                            }

                        }else{
                            self::logtxt("1-El archivo no existe o no se pudo crear");
                            var_dump('1-El archivo no existe o no se pudo crear');
                        }

                    }else {
                        self::logtxt("No hay nuevos registros para enviar...");
                        var_dump('No hay nuevos registros para enviar...');
                    }


                } // fin si o no hay registros en el historico
                
                
            }else {
                // var_dump('no funcional!');
            } // fin si o no la url viene con una llave

        } // fin si el modulo esta en live
        
    } // fin function
}
