<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class NewsletterPopup extends Module
{
    public function __construct()
    {
        $this->name = 'newsletterpopup';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Mateusz Wojnowski';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Newsletter Popup');
        $this->description = $this->l('Moduł wyświetlający popup do zapisu na newsletter.');

        $this->confirmUninstall = $this->l('Na pewno chcesz odinstalować?');

        if (!Configuration::get('NEWSLETTERPOPUP_TITLE') ||
            !Configuration::get('NEWSLETTERPOPUP_CONTENT') ||
            !Configuration::get('NEWSLETTERPOPUP_SUBMIT')
        ) {
            $this->warning = $this->l('błąd konfiguracji');
        }
    }
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $dbhost = "localhost";
        $dbname = "prestashop";
        $dbuser = "root";
        $dbpassword = "";
        $db_conn = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
        $sqlcreatetable = 'CREATE TABLE newsletter(
        id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(30) NOT NULL
        )';
        $db_conn->query($sqlcreatetable);



        return parent::install() &&
            $this->registerHook('displayHome') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            Configuration::updateValue('NEWSLETTERPOPUP_TITLE', 'Zapisz się na newsletter!') &&
            Configuration::updateValue('NEWSLETTERPOPUP_CONTENT', 'Dzięki temu nigdy nie ominą Cię wyjątkowe okazje') &&
            Configuration::updateValue('NEWSLETTERPOPUP_SUBMIT', 'Zapisuję się!')
            ;
    }
    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('NEWSLETTERPOPUP_TITLE') ||
            !Configuration::deleteByName('NEWSLETTERPOPUP_CONTENT') ||
            !Configuration::deleteByName('NEWSLETTERPOPUP_SUBMIT')
        ) {
            return false;
        }

        return true;
    }
    public function displayForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Ustawienia'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Tytuł'),
                    'name' => 'NEWSLETTERPOPUP_TITLE',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Tekst pod tytułem'),
                    'name' => 'NEWSLETTERPOPUP_CONTENT',
                    'size' => 40,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Tekst na przycisku zapisującym'),
                    'name' => 'NEWSLETTERPOPUP_SUBMIT',
                    'size' => 20,
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();


        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;


        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;


        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];


        $helper->fields_value['NEWSLETTERPOPUP_TITLE'] = Tools::getValue('NEWSLETTERPOPUP_TITLE', Configuration::get('NEWSLETTERPOPUP_TITLE'));
        $helper->fields_value['NEWSLETTERPOPUP_CONTENT'] = Tools::getValue('NEWSLETTERPOPUP_CONTENT', Configuration::get('NEWSLETTERPOPUP_CONTENT'));
        $helper->fields_value['NEWSLETTERPOPUP_SUBMIT'] = Tools::getValue('NEWSLETTERPOPUP_SUBMIT', Configuration::get('NEWSLETTERPOPUP_SUBMIT'));

        return $helper->generateForm($fieldsForm);
    }
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $newsletterPopupTitle = strval(Tools::getValue('NEWSLETTERPOPUP_TITLE'));
            $newsletterPopupContent = strval(Tools::getValue('NEWSLETTERPOPUP_CONTENT'));
            $newsletterPopupSubmit = strval(Tools::getValue('NEWSLETTERPOPUP_SUBMIT'));

            if (
                !$newsletterPopupTitle ||
                empty($newsletterPopupTitle) ||
                !Validate::isGenericName($newsletterPopupTitle) ||
                !$newsletterPopupContent ||
                empty($newsletterPopupContent) ||
                !Validate::isGenericName($newsletterPopupContent) ||
                !$newsletterPopupSubmit ||
                empty($newsletterPopupSubmit) ||
                !Validate::isGenericName($newsletterPopupSubmit)
            ) {
                $output .= $this->displayError($this->l('błąd konfiguracji'));
            } else {
                Configuration::updateValue('NEWSLETTERPOPUP_TITLE', $newsletterPopupTitle);
                Configuration::updateValue('NEWSLETTERPOPUP_CONTENT', $newsletterPopupContent);
                Configuration::updateValue('NEWSLETTERPOPUP_SUBMIT', $newsletterPopupSubmit);
                $output .= $this->displayConfirmation($this->l('dane zaktualizowane'));
            }
        }

        return $output.$this->displayForm();
    }
    public function hookDisplayHome ($params)
    {

        if ($_POST['name'] && $_POST['email']) {
            $dbhost = "localhost";
            $dbname = "prestashop";
            $dbuser = "root";
            $dbpassword = "";
            $db_conn = new PDO("mysql:host=".$dbhost.";dbname=".$dbname, $dbuser, $dbpassword);
            $sql = 'INSERT INTO newsletter (name, email)
                VALUES (:name, :email)';
            $stmt = $db_conn->prepare($sql);
            $stmt->execute(array(
                    ':name' => $_POST['name'],
                    ':email' => $_POST['email'])
            );

        }

        $this->context->smarty->assign([
                'newsletterpopup_title' => Configuration::get('NEWSLETTERPOPUP_TITLE'),
                'newsletterpopup_content' => Configuration::get('NEWSLETTERPOPUP_CONTENT'),
                'newsletterpopup_submit' => Configuration::get('NEWSLETTERPOPUP_SUBMIT'),
                'newsletterpopup_link' => $this->context->link->getModuleLink('newsletterpopup', 'display')
            ]
        );

        return $this->display(__FILE__, 'newsletterpopup.tpl');
    }
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->addCSS($this->_path.'views/css/newsletterpopup.css');
        $this->context->controller->addJS($this->_path.'views/js/newsletterpopup.js');
    }

}