<?php
/**
* Name: Export to Pdf
* Description: This is a new feature developed by bruno sousa (peakit), that make export a campaing to a pdf
* Minimum Oempro Version: 4.1.0
*/
class exportpdf extends Plugins
{
	public static $arrayLanguage = array();
	public static $ObjectCI;
	private static $arrayParsedRSS = array();
	private static $subject = '';
	private static $htmlBody = '';
	private static $plainBody = '';
	private static $tags = array();
	
	function __construct() {}

	function enable_exportpdf() {
		Database::$Interface->SaveOption('ExportPdf_Language', 'en');
	}

	function disable_exportpdf() {
		Database::$Interface->RemoveOption('ExportPdf_Language');
	}

	function load_exportpdf() {
		parent::RegisterEnableHook('exportpdf');
		parent::RegisterDisableHook('exportpdf');


        // Hooks - Start {
        parent::RegisterMenuHook('exportpdf', 'RegisterMenuItems');
        // Hooks - End }

		#parent::RegisterHook('Filter', 'Email.Send.Before', 'octrss', 'detectRSSTags', 10, 3);
		#parent::RegisterHook('Filter', 'PersonalizationTags.Campaign.Content', 'octrss', 'getPersonalizationTags', 10, 0);
		#parent::RegisterHook('Filter', 'PersonalizationTags.Autoresponder.Content', 'octrss', 'getPersonalizationTags', 10, 0);

		$languageOption = Database::$Interface->GetOption('ExportPdf_Language');


		$language = $languageOption[0]['OptionValue'];

        $ArrayPlugInLanguageStrings = array();
		if (file_exists(PLUGIN_PATH.'exportpdf/languages/'.strtolower($language).'/'.strtolower($language).'.inc.php') == true) {
			include_once(PLUGIN_PATH.'exportpdf/languages/'.strtolower($language).'/'.strtolower($language).'.inc.php');
		} else {
			include_once(PLUGIN_PATH.'exportpdf/languages/en/en.inc.php');
		}
		self::$arrayLanguage = $ArrayPlugInLanguageStrings;
		unset($ArrayPlugInLanguageStrings);
	}


    /**
     * Registers menu items for the user interface
     *
     * @param string $ArrayMenuItems
     * @return void
     * @author Cem Hurturk
     */
    function RegisterMenuItems($ArrayMenuItems, $parameters)
    {
        $ArrayMenuItems[] = array(
            'MenuLocation'        => 'Campaign.Navigation.Options',
            'MenuID'                => 'ExportToPDF',
            'MenuLink'                => Core::InterfaceAppURL().'/exportpdf/pdf/' . $parameters['CampaignID'],
            'MenuTitle'                => self::$arrayLanguage['0001'],
        );
        return array($ArrayMenuItems);
    }

    function _Header()
    {
        self::$ObjectCI =& get_instance();

        if (Plugins::IsPlugInEnabled('exportpdf') == false)
        {
            // Display error message
            $Message = ApplicationHeader::$ArrayLanguageStrings['0002'];
            self::$ObjectCI->display_public_message($Error, $Message);
            return false;
        }

        self::_CheckAuth();

        return true;
    }

    function _CheckAuth()
    {
        // Load other modules - Start
        Core::LoadObject('user_auth');
        // Load other modules - End

        // Check the login session, redirect based on the login session status - Start
        UserAuth::IsLoggedIn(false, InterfaceAppURL(true).'/user/');
        // Check the login session, redirect based on the login session status - End

        return;
    }

	function getLanguage() {
		return self::$arrayLanguage;	
	}

    // ---------------------=[ Controller Functions - Start ] {
    function ui_pdf($id){
        if (self::_Header() == false) return;
        #echo $id;
        $ArrayAPIData = array('campaignid' => $id);
        $ArrayOutput = array();
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        #print_r($ArrayUserInformation);
        #self::$ObjectCI->load->helper('url');
        #self::$ObjectCI->load->helper('form');
        #include_once(PLUGIN_PATH.'../includes/api/campaign.get.inc.php');
        #print_r($ArrayOutput);echo 'passei';
        Core::LoadObject('campaigns');

        $ArrayCampaign	= Campaigns::RetrieveCampaigns_Enhanced(
            array(
                'Criteria'	=> array(
                    #array('Column' => '%c%.RelOwnerUserID', 'Operator' => '=', 'ValueWOQuote' => $ArrayUserInformation['UserID']),
                    array('Column' => '%c%.CampaignID', 'Operator' => '=', 'ValueWOQuote' => $ArrayAPIData['campaignid'], /*'Link' => 'AND'*/ )
                ),
                'Content'	=> true,
                'SplitTest'	=> true,
                #'Reports'	=> $ArrayStatisticsOptions
            ));
        if (count($ArrayCampaign) < 1)
        {
            self::$ObjectCI->load->helper('url');
            redirect(InterfaceAppURL(true), 'location', '302');return;
        }
        else
        {
            $ArrayCampaign = $ArrayCampaign[0];
        }
        #echo '<pre>';

        $newsletter = $ArrayCampaign['Email'];
        $content = '';
        if(strtoupper($newsletter['ContentType'])  == 'HTML'){
            $content = $newsletter['HTMLContent'];
        }else{
            $content = $newsletter['PlainContent'];
        }
        /*$content = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', ' ',  $content);*/
        #echo $content;exit;

        include(PLUGIN_PATH. 'exportpdf/includes/wkhtmltopdf.php');
        $wkpdf = new WKPDF();

        $wkpdf->set_html($content);
        $wkpdf->render();
        $wkpdf->output('D', 'campaign_'.$id.'.pdf');
        /*
        include(PLUGIN_PATH. 'exportpdf/includes/mpdf60beta/mpdf.php');

        $mpdf=new mPDF(
            $mode='',
            $format='A4',
            $default_font_size=0,
            $default_font='',
            $mgl=15,
            $mgr=15,
            $mgt=16,
            $mgb=16,
            $mgh=9,
            $mgf=9,
            $orientation='P'
        );
        #$mpdf->autoScriptToLang = True;

        #$mpdf->SetDisplayMode('fullpage');


// LOAD a stylesheet
        #$stylesheet = file_get_contents('mpdfstyleA4.css');
        #$mpdf->WriteHTML($stylesheet,1);	// The parameter 1 tells that this is css/style only and no body/html/text

        $mpdf->WriteHTML($content);

        $mpdf->Output();
        */
        #print_r($content);

        #echo '</pre>';
        #Campaigns::
    }

    function ui_index()
    {
        if (self::_Header() == false) return;
        self::$ObjectCI->load->helper('url');
        redirect(InterfaceAppURL(true), 'location', '302');return;
    }
    // ---------------------=[ Controller Functions - End ] }
}


?>
