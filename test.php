<?php

class Test extends MY_Controller {

    public function __construct() {

        parent::__construct();

        if ($this->nativesession->get('is_logged_in') == false) {
            $current_url['redirect'] = current_url();
            $this->nativesession->set($current_url);
            redirect('login');
        }


        if (!$this->nativesession->get('last_x_days_voucher')) {
            $config_variable_array = $this->master_model->get_master("global_config_variable", "gcv_name='number_of_days_last_downloaded'");
            $last_x_days_voucher['last_x_days_voucher'] = $config_variable_array[0]['gcv_value'];
            $this->nativesession->set($last_x_days_voucher);
        }
        $this->load->helper('filter_helper');
        $this->load->library('my_pagination');
        $this->load->model('company_model');
        //$this->output->enable_profiler(TRUE);
    }

    public function testAny(){
        echo anchor('news/local/123', '<div id="first">My News</div>', 'title="News title"');
    }

    public function uploadToCloud() {
        $params = array("username" => "rahmanhussain", "api_key" => "32dc9fb26dee16437d58d8d82e5e3e97", "account" => NULL, "auth_host" => NULL);
        $this->load->library('cloudfiles/cf_authentication', $params);
        $auth = $this->cf_authentication;
        $this->cf_authentication->authenticate();
        $conne = new CF_Connection($auth);



        $cont = $conne->get_container('images');




        $objects = $cont->list_objects();
        foreach ($objects as $obj) {

            //        $result = $cont->delete_object($obj);
            //      p($result);
        }


        $result = $cont->delete_object('150px');
        p($result);die;





        die;
    }

    public function testPurge(){

        $fields = array(
            'a' => 'fpurge_ts',
            'tkn' => '2943d9f88a2500a020881237fb00a7269bf5b',
            'email' => 'abdulrahman281@gmail.com',
            'z' => 'vouchercodesuae.com',
            'v' => '1',
            'url' => 'http://www.vouchercodesuae.com/emirates.com'
        );

        $fields_string = "";
        //url-ify the data for the POST
        //url-ify the data for the POST
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');


        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, 'https://www.cloudflare.com/api_json.html');
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        var_dump($result);
        die;
        //close connection
        curl_close($ch);


    }

    public function update_name(){

        $table_name = 'members';
        $select = "*";
        $where = "firstname like '%?%'";
        $users_result = $this->master_model->get_master($table_name, $where, FALSE, 'DESC', 'id', $select);
        foreach ($users_result as $value) {
            echo $value['email'];
            $email_part = explode("@", $value['email']);
            $wherenew['email'] = $value['email'];
            $subscriber_name = preg_replace('/\d/', '', $email_part[0] );
            $update['firstname'] = $subscriber_name;
            $update['surname'] = "";
            $this->master_model->master_update($update, 'members', $wherenew);
            echo $this->db->last_query();
        }

    }

    public function update_company_voucher_status(){

        $select = "CompanyID";
        $table = "company";
        $where = "CompanyID >0 ";
        $company_ids  = $this->master_model->get_master($table, $where, $AdminJoin = FALSE, $order = false, $field = false, $select);
        foreach($company_ids as $key => $id){

            $company_id = $id['CompanyID'];
            //set voucher status
            $where_active_voucher_company = "CompanyID='" . $company_id . "' AND ProductEndDate>='" . date('Y-m-d 00:00:00')."'";
            $active_vouches = $this->master_model->master_get_num_rows('product', $where_active_voucher_company);
            $update_voucher_status['voucher_status'] = $active_vouches;
            $where_update= array('CompanyID' => $company_id);
            $this->master_model->master_update($update_voucher_status, 'company', $where_update);
            q();
            echo "<br>";
        }
    }

    
    public function update_exclusive_voucher(){
        
        $select = "ProductID";
        $table = "product";
        $where = "product.ProductEndDate >= CURDATE()";
        $join = array(
            'company'=>'product.CompanyID = company.CompanyID | LEFT',
        );
        echo "updating..";
        $product_ids  = $this->master_model->get_master($table, $where, $join, $order = false, $field = false, $select);
        echo $this->db->last_query();
        var_dump($product_ids);
        foreach($product_ids as $key => $id){
            $this->master_model->update_product_to_algolia($id['ProductID']);
            echo $id['ProductID'];
             echo "<br>";
        }
    }
    public function testDate(){
        $select = "CompanyID";
        $table = "company";
        $where = "CompanyID >0 ";
        $users  = $this->master_model->get_master($table, $where, $AdminJoin = FALSE, $order = false, $field = false, $select);
        foreach($users as $user){
            $members = array();
            $company_id = $id['CompanyID'];


            $members['firstname'] = "";
            $members['lastname'] = "";


            $name = $user['name'];
            $name =  "Samer Abou Daher";
            $arrayname = explode( " ", $name);
            if($arrayname[0]){
                $members['firstname'] = $arrayname[0];
            }
            if($arrayname[0]){
                $members['lastname'] = $arrayname[1];
            }

            $members['username'] = $user['firstname'].$user['lastname'];
            $members['givenname'] = $user['firstname'].$user['lastname'];
            $members['registeredate'] = $user['date'];
            $members['email'] = $user['email'];

            p($members);die;


            //check for exists email id
            $where_email = "email='" . $user['email'] . "'";
            $active_vouchers = $this->master_model->master_get_num_rows('members', $active_vouches);
            if($active_vouchers == 0){

                $this->db->insert('members', $members);
            }
            q();
            echo "<br>";
        }

    }

    public function ini(){
        phpinfo();
    }

    public function move_competition_users(){
        //  $select = "CompanyID";
        $table = "competition";
        $where = false;
        $users  = $this->master_model->get_master($table, $where, $join = FALSE, $order = false, $field = false, $select=false);

        foreach($users as $user){

            $members = array();
            $members['firstname'] = "";
            $members['surname'] = "";


            $name = $user['name'];
            echo $name;
            $arrayname = explode( " ", $name);
            p($arrayname);
            if(isset($arrayname[1])){
                $members['firstname'] = $arrayname[1];
            }
            if(isset($arrayname[2])){
                $members['surname'] = $arrayname[2];
            }

            $members['username'] = $members['firstname'].$members['surname'];
            $members['given_name'] = $members['firstname'].$members['surname'];
            $members['registerdate'] = $user['date'];
            $members['email'] = $user['email'];

            p($members);

            //check for exists email id
            $where_email = "email='" . $user['email'] . "'";
            $exist= $this->master_model->master_get_num_rows('members', $where_email);


            if($exist == 0){
                $this->db->insert('members', $members);

                q();
            }
            unset($members);
            echo "<br>";
        }
    }

    public function snedTestMail() {

        $to = "x@mail.asana.com";
        $from = "rahman@dcmjlt.com";
        $msg = "Send from system by abhay";
        $headers = "From: rahman@dcmjlt.com" . "\r\n" .

            $subject = "Test msg from system";
        mail($to,$subject,$msg,$headers);
        echo "sent";die;

    }

    public function testingReport(){
        $this->load->library('my_pagination');
        
         $config['base_url'] = base_url() . "test/testingReport";
        $perpage = PAGINATION_PER_PAGE;
        $this->my_pagination->per_page = $perpage;
        $config = $this->my_pagination->pagination_config($config);

        
        

        $data["page_title"]     = $this->page_title     = "Testing Reports ";
        $data['base_url']       = base_url();


        // Search
        $where = " 1=1 ";
        if (isset($_GET['start_date']) && !empty($_GET['end_date'])) {
            $where .= " AND created >= '".date('Y-m-d H:i:s',strtotime($_GET['start_date']))."' AND created <= '".date('Y-m-d H:i:s',strtotime($_GET['end_date']))."' ";
        }else{
            $where .= " AND YEARWEEK(created,1) = ".date('oW')." ";
        }

        $where .= " GROUP BY user_id ";
        $join = array(
            'adminuser'=>'adminuser.id = testing_records.user_id | LEFT',
            'resp_usr_grp_mapping'=>'resp_usr_grp_mapping.admin_id = testing_records.user_id  AND resp_usr_grp_mapping.resp_group_id IN (6,7,8) | LEFT',
            'responsible_groups'=>'resp_usr_grp_mapping.resp_group_id = responsible_groups.rg_id | LEFT',
            'testing_groups' => 'testing_groups.responsible_group_id = responsible_groups.rg_id|LEFT',
            'global_config_variable' => 'global_config_variable.gcv_id = testing_groups.gcv_id|LEFT'
        );
        $select = "testing_records.user_id,
            adminuser.userName,
            resp_usr_grp_mapping.resp_group_id,
            SUM(testing_time) as total_testing_time,
            SUM(issues_found) as total_issues_found,
            responsible_groups.rg_name,
            global_config_variable.gcv_value as minutes_per_week";
        $data['users']  = $this->master_model->get_master('testing_records',$where,$join,FALSE,FALSE,$select);
        
        $config['total_rows'] = count($data['users']);
        $this->my_pagination->initialize($config);
        //q(88);
        foreach($data['users'] as $user){
            $where = " testing_records.user_id = ".$user['user_id']." ";
            if (isset($_GET['start_date']) && !empty($_GET['end_date'])) {
                $where .= " AND created >= '".date('Y-m-d H:i:s',strtotime($_GET['start_date']))."' AND created <= '".date('Y-m-d H:i:s',strtotime($_GET['end_date']))."' ";
            }else{
                $where .= " AND YEARWEEK(created,1) = ".date('oW')." ";
            }
            $where .= " GROUP BY testing_device ";
            $select = "testing_records.user_id,
            SUM(testing_time) as total_testing_time,
            SUM(issues_found) as total_issues_found,
            testing_device";

            $result  = $this->master_model->get_master('testing_records',$where,NULL,FALSE,FALSE,$select);
            foreach ($result as $val){
                $data['result'][$val['user_id']][] =  $val;
            }
        }

        
        //p($data);
        /*q(88);*/
        $data['total_rows'] = count( $data['users']);
        //p($data);die();
        $data['base_url'] = $config['base_url'];
        if (IS_AJAX) {
            $data['is_ajax'] = TRUE;
            $this->load->view("admin/report/testing_report",@$data);
        } else {
            $this->load->view('admin/head', $data);
            $this->load->view('admin/report/testing_report', $data);
            $this->load->view('admin/footer');
        }
    }

    public function startTesting(){
        $nextURI = isset($_GET['nextURL']) ? $_GET['nextURL'] : base_url().'/dashboard';

        $this->load->library('Mobile_Detect');
        if($this->mobile_detect->isMobile())
            $device = 'Mobile';
        elseif ($this->mobile_detect->isTablet())
            $device = 'Tablet';
        else
            $device = 'Desktop';
        $testing = array(
            'startTime' => time(),
            'device'   => $device,
        );
        $this->session->set_userdata('site_testing' ,$testing);
        //print_r($_SESSION['site_testing']);die();

        redirect($nextURI);
    }

    public function endTesting(){
        $nextURI = isset($_GET['nextURL']) ? $_GET['nextURL'] : FRONT_SITE.'test/endTesting';
        $postData = $this->input->post();

        $data['user_id'] = $this->nativesession->get('admin_id');
        $data['testing_time'] = $postData['totalSeconds'] - $postData['totalIdleSeconds'];
        $data['issues_found'] = $postData['issues_found'];
        $data['testing_device'] = $_SESSION['site_testing']['device'];
        $data['created'] = date('Y-m-d H:i:s');

        $this->master_model->master_insert($data,'testing_records');

        $site_testing = $this->nativesession->get('site_testing');
        $this->nativesession->unset_key('site_testing');

        $previous_week = strtotime("-1 week +1 day");

        $start_week = strtotime("last monday midnight", $previous_week);
        $end_week = strtotime("next sunday", $start_week);

        $start_week = date("Y-m-d H:i:s", $start_week);
        $end_week = date("Y-m-d H:i:s", $end_week);

        $where = "user_id = ".$data['user_id']." AND created >= '$start_week' AND created <= '$end_week'";
        $select = "SUM(testing_time) as total_testing_time";
        $testingtime  = $this->master_model->get_master('testing_records',$where,FALSE,FALSE,FALSE,$select);
        $allocatedTime = ($testingtime[0]['total_testing_time']) ? $this->input->post('allocated_time')  : 0  ;

        //User logs Entry
        $logs_array = array(
            'entry_time' => (time() - $data['testing_time']) ,  // delay start time by total idle seconds .
            'type' => 'testing',
            'action' => 'testing',
            'action_id' => 152,
            'allocated_time' =>$allocatedTime
        );
        insert_user_logs($logs_array);



        header("Access-Control-Allow-Origin: ".FRONT_SITE.'test/endTesting');
        //p($this->session->all_userdata());
        echo 1;
        //redirect($nextURI);
    }

    public function updateSupplierLinks($offset = 0){
        $company = $this->master_model->get_custom_query_result("SELECT CompanyID, CompanyWebsite,direct_link FROM (`company`) WHERE CompanyID =  1546");

        $site_supplier_campaigns = $this->master_model->get_custom_query_result("SELECT supplier_id, campaign_id FROM (`site_supplier_campaign`) WHERE site_id = 1");
        if(!count($company)){
            die('No companies to display !!!');
        }
        
        foreach($company as $c){
            echo $c['CompanyID'] ."==>". $c['CompanyWebsite']."<br>";
            $directLinkFlag = 1;
            $setOnline= 0;
            foreach ($site_supplier_campaigns as $sc){
                if(strpos($c['CompanyWebsite'], $sc['campaign_id'] ) === FALSE){
                    //echo "No Match ! Company Link is : ".$c['CompanyWebsite'].'<br>';
                    continue;

                }else{
                    $directLinkFlag = 0;
                    echo 'Match Found !! Company ID : '.$c['CompanyID'].' Matches with Supplier '.$sc['supplier_id'].'. ....  Updating Supplier Link ....<br>';
                    echo "Set Company Csv_date -> link_type = ".$c['direct_link']."<br>";
                    $updateData['link_type'] = $c['direct_link'];
                    echo "Set Company Csv_date -> supplier_link = ".$c['CompanyWebsite']."<br>";
                    $updateData['supplier_link'] = $c['CompanyWebsite'];
                    echo "Set Company Csv_date -> is_active = 1 <br>";

                    $updateData['is_active'] = '1';
                    
					if($setOnline == 0)
                        $updateData['is_online'] = '1';
                        
					$setOnline= 1;

                    $count = $this->master_model->get_custom_query_result("SELECT COUNT(*) as count FROM company_csv_data WHERE supplier_name = '".$sc['supplier_id']."' AND CompanyID = '".$c['CompanyID']."' ");
                    //p($count[0]['count']);
                    if($count[0]['count']){
                        $this->master_model->master_update($updateData,'company_csv_data', " supplier_name = '".$sc['supplier_id']."' AND CompanyID = '".$c['CompanyID']."' ");
                    }else{
                        $updateData['supplier_name'] = $sc['supplier_id'];
                        $updateData['CompanyID'] = $c['CompanyID'];
                        $this->master_model->master_insert($updateData,'company_csv_data');
                    }

                    echo "No. Of Rows Affected : ".$this->db->affected_rows()."<br>";
                    echo "<br>--------------------------------------------------------<br>";
                    //echo 'Updated Record !!<br>';*/
                }

            }

            if($directLinkFlag){

                echo " This is a direct Link ... checking if link exist in table <br>";
              
                $this->master_model->master_delete('company_csv_data',  "CompanyID = '".$c['CompanyID']."' AND supplier_link = '".$c['CompanyWebsite']."'");





                    echo " No direct Link found Adding Direct Link..<br>";
                    $updateData['CompanyID'] = $c['CompanyID'];
                    $updateData['supplier_link'] = $c['CompanyWebsite'];
                    $updateData['link_type'] = 1;
					$updateData['is_online'] = 1;
                    $updateData['is_active'] = '1';
                    $this->master_model->master_insert($updateData,'company_csv_data');
                    echo "No. Of Rows Affected : ".$this->db->affected_rows()."<br>";
           
            }
        }
    }

    public function check_date_from_sec($time = ""){
        echo strftime('%D - %H:%M:%S', $time);
    }

    public function updateDirectLinksInCompanyTable($companyID = 0){

		$company = $this->master_model->get_custom_query_result("SELECT CompanyID, CompanyWebsite,direct_link FROM (`company`)");
		foreach($company as $c){
			$where = "is_online = '1' AND CompanyID = ".$c['CompanyID'];
			$result = $this->master_model->get_master('company_csv_data',$where,FALSE,FALSE,FALSE,$select = "CompanyID, link_type");
echo $this->db->last_query();
            if($result){
          

				foreach($result as $r){


								$updateData['direct_link_status'] = $r['link_type'];
								$updateData['direct_link'] = $r['link_type'];
								$where = array();
								$where['CompanyID'] = $r['CompanyID'];
							    $this->master_model->master_update($updateData, 'company', $where);
								echo $this->db->last_query();

				}
			}
        
		}

    }


    public function checkServerDateTime(){
        echo date("Y-m-d h:i:s");

    }

    public function showAdminModulePermission(){ // userdata['admin_url']
        $admin_url_array=json_decode($_SESSION['admin_url'],true);
        p($admin_url_array);die("<br> User Auth access Level ");
    }
    
    public function check_url_response_from_curl(){
        
        $url = "http://dev.vouchercodesuae.com/accorhotels.com"; 
        echo "url is - ".$url;
        $options = array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => true, // return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_ENCODING => "", // handle all encodings
        CURLOPT_USERAGENT => "spider", // who am i
        CURLOPT_AUTOREFERER => true, // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
        CURLOPT_TIMEOUT => 120, // timeout on response
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
    );
    
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        print_r($content);
        //print_r($err);
        //print_r($errmsg);
        //print_r($header);
    }
    
    public function getServerIP(){
        echo $current_ip = trim(shell_exec('hostname -i'));
    }
    
    
    public function assign_supplier_admin_to_company(){
        
        $company = $this->master_model->get_custom_query_result("SELECT CompanyID, CompanyWebsite,direct_link FROM (`company`) WHERE CompanyID > 0 ");
        if(!count($company)){
            die('No companies to display !!!');
        }
        
        foreach($company as $c){
					$company_id =  $c['CompanyID'];
			        $supplier = $this->master_model->get_custom_query_result("SELECT company_csv_data.*, supplier.userid as supplier_assigned_user  FROM company_csv_data left join supplier on supplier.id = company_csv_data.supplier_name WHERE is_online =1 AND is_active = 1 AND CompanyID = $company_id  limit 1");
					
					if($supplier ){
						$supplier = $supplier[0];
						if($supplier['supplier_assigned_user'] && $supplier['supplier_assigned_user'] != ''){
							echo "<br>Updating  company admin for company id : ".$company_id." as admin id ".$supplier['supplier_assigned_user']."<br>" ;
								$updateData = array();
								$companyUpdateData['company_admin'] = $supplier['supplier_assigned_user'];

								$this->master_model->master_update($companyUpdateData,'company', " CompanyID = '".$company_id."'");
								echo $this->db->last_query();
								echo "<br>";


						}

					}

        }
    }


		function update_company_websitelink(){
		$companies = $this->master_model->get_master('company',$where = "CompanyWebsite =0 ",FALSE,FALSE,FALSE,$select = "CompanyID");
		 $i = 0;
			foreach($companies as $comapny){
				echo $i."<br>";

				$where = "is_online =1 AND is_active = 1 AND CompanyID = ".$comapny['CompanyID']; 
				$supplier =  $this->master_model->get_master_row('company_csv_data', $select = FALSE, $where, $join = FALSE);
				echo $supplier['supplier_link'];
				echo "<br>";
				$companyUpdateData['CompanyWebsite'] = $supplier['supplier_link'];
				$this->master_model->master_update($companyUpdateData,'company', " CompanyID = ".$comapny['CompanyID']."");
				$i++;
			}
	}
}

