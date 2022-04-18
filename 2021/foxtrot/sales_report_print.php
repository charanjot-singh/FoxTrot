<?php 

require_once("include/config.php");
require_once(DIR_FS."islogin.php");

$instance = new batches();

//DEFAULT PDF DATA:
$get_logo = $instance->get_system_logo();
$system_logo = isset($get_logo['logo'])?$instance->re_db_input($get_logo['logo']):'';
$get_company_name = $instance->get_company_name();
$system_company_name = isset($get_company_name['company_name'])?$instance->re_db_input($get_company_name['company_name']):'';
$instance_trans = new transaction();

$return_batches = array();
$filter_array = array();
$product_category = '';
$product_category_name = '';
$beginning_date = '';
$ending_date = '';

//filter batch report
if(isset($_GET['filter']) && $_GET['filter'] != '')
{
    $filter_array = json_decode($_GET['filter'],true);
    $product_category = isset($filter_array['product_category'])?$filter_array['product_category']:0;
    $company = isset($filter_array['company'])?$filter_array['company']:0;
    $batch = isset($filter_array['batch'])?$filter_array['batch']:0;
    $branch = isset($filter_array['branch'])?$filter_array['branch']:0;
    $broker = isset($filter_array['broker'])?$filter_array['broker']:0;
    $client = isset($filter_array['client'])?$filter_array['client']:0;
    $product = isset($filter_array['product'])?$filter_array['product']:0;
    $beginning_date = isset($filter_array['beginning_date'])?$filter_array['beginning_date']:'';
    $ending_date = isset($filter_array['ending_date'])?$filter_array['ending_date']:'';
    $sort_by = isset($filter_array['sort_by'])?$filter_array['sort_by']:'';
    $filter_by= isset($filter_array['filter_by'])?$instance->re_db_input($filter_array['filter_by']):1;
    $prod_cat = isset($filter_array['prod_cat'])?$filter_array['prod_cat']:array();
    $report_for = isset($filter_array['report_for'])?trim($filter_array['report_for']):'';
    $is_trail= isset($filter_array['is_trail'])?$instance->re_db_input($filter_array['is_trail']):0;
    $sponsor = isset($filter_array['sponsor'])?$instance->re_db_input($filter_array['sponsor']):'';
    $date_by= isset($filter_array['date_by'])?$instance->re_db_input($filter_array['date_by']):1;
    $product_cate= isset($filter_array['product_cate'])?$instance->re_db_input($filter_array['product_cate']):'';
    $earning_by= isset($filter_array['earning_by'])?$instance->re_db_input($filter_array['earning_by']):1;

    $subheading=strtoupper($report_for)." REPORT ";
    
}   
   if(isset($filter_array['date_earning_by'])) {
        $earning_by = $instance->re_db_input($filter_array['date_earning_by']);
    }
    $report_rank_order_by = $filter_array['report_rank'] ?? 0;
    $broker_type = $filter_array['broker_type'] ?? 0;
    $top_broker_count = $filter_array['top_broker_count'] ?? 1;

    $report_year = isset($filter_array['report_year'])?trim($filter_array['report_year']):date("Y");
    //
    $annul_broker_date_type = $filter_array['annual-broker-date-type'] ?? 0; 
   
    if($company > 0){
            //$branch_instance = new branch_maintenance();
            $instance_multi_company = new manage_company();
            $name  = $instance_multi_company->select_company_by_id($company); 
            $companyhead=$name['company_name'];
            //$subheading.="\r Broker: (ALL Brokers), Client: (ALL Clients)";
        }
        else{
              $companyhead="All Companies";
             // $subheading.="\r Broker: (ALL Brokers), Client: (ALL Clients)";
        }
   
   
    $subheading=strtoupper($report_for);
    if($filter_by == "1"){
       // $subheading2=$beginning_date." thru ".$ending_date;

    }
    if($sort_by=="1")
    {
        $subheading1="Sort by Sponsor";
    }
    else if($sort_by=="2")
    {
        $subheading1="Sort by Investment Amount";
    } 

   
    if($report_for == "sponsor"){
        if($sponsor > 0){
            $name  = $instance_trans->select_sponsor_by_id($sponsor); 
            $subheading.="<br/> FOR ".strtoupper($name);
            $subheading.="<br/>Broker: (All Brokers), Client: (All Clients)";
        }
        else{
              $subheading.="<br/> FOR All SPONSORS";
              $subheading.="<br/>Broker: (All Brokers), Client: (All Clients)";
        }
    }
    if($report_for == "branch"){
        if($branch > 0){
            $branch_instance = new branch_maintenance();
            $name  = $branch_instance->select_branch_by_id($branch); 
            $subheading.="<br/> FOR ".strtoupper($name['name']);
            $subheading.="<br/>Broker: (All Brokers), Client: (All Clients)";
        }
        else{
              $subheading.="<br/> FOR All BRANCHES";
              $subheading.="<br/>Broker: (All Brokers), Client: (All Clients)";
        }

    }
    if($report_for == "batch"){
        if($batch > 0){
            $branch_instance = new batches();

            $name  = $branch_instance->edit_batches($batch);
           
            $subheading.="\r FOR ".strtoupper($name['batch_desc']);
            $subheading.="\r Broker: (All Brokers), Client: (All Clients)";
        }
        else{
              $subheading.="\r FOR All BATCHES";
              $subheading.="\r Broker: (All Brokers), Client: (All Clients)";
        }

    }
    if($report_for == "client"){
        if($client > 0){
            $branch_instance = new client_maintenance();
            $name  = $branch_instance->select_client_master($client); 

            $subheading.="\r FOR ".strtoupper($name['last_name'].', '.$name['first_name'])."<br/>";
            //$subheading.="<br/>Broker: (All Brokers), Client: (All Clients)";
        }
        else{
              $subheading.="\r FOR All CLIENTS <br/>";
            //  $subheading.="<br/>Broker: (All Brokers), Client: (All Clients)";
        }

    }
   
    if($report_for =="year_to_date"){
           $report_year = isset($filter_array['report_year'])?trim($filter_array['report_year']):date("Y");
            $beginning_date = date('Y-m-d', strtotime('first day of january '.$report_year));
            $ending_date = date('Y-m-d', strtotime('last day of december '.$report_year));
            $heading ="All Companies";
            if($company > 0){
                $heading=$companyhead;
            }
            $subheading=" YEAR-TO-DATE EARNINGS REPORT ";
            $without_earning= isset($filter_array['without_earning'])?$instance->re_db_input($filter_array['without_earning']):'';
            $get_trans_data = $instance_trans->select_year_to_date_sale_report($beginning_date,$ending_date,$company,$without_earning,$earning_by);
             // create new PDF document
            $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // add a page
            $pdf->AddPage('L');
            // Title
            $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="40px" />';
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);

            $html='<table border="0" width="100%">
                        <tr>';
                         $html .='<td width="20%" align="left">'.date("m/d/Y").'</td>';
                        
                        $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">'.$img.'<br/><strong><h9>'.$subheading.'<br/>Year '.$report_year.', '.$companyhead.'<br/>'.$subheading2.'<br/>'.$subheading1.'</h9></strong></td>';
                                         
                            $html.='<td width="20%" align="right">Page 1</td>';
                        
                        $html.='</tr>
                </table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
            $html='<table border="0">
                        <tr>';
                            $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;"></td>';
                    $html .='</tr>
                    </table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(2);
            
            $pdf->SetFont('times','B',12);
            $pdf->SetFont('times','',10);
             $html='<table border="0" cellpadding="1" width="100%">
                <tr style="background-color: #f1f1f1;">';
                
                 
                $html.=' <td style="text-align:left;font-weight:bold;"><h5>BROKER </h5></td>
                       
                        <td style="text-align:left;font-weight:bold;"><h5>NUMBER </h5></td>
                       
                        <td style="text-align:right;font-weight:bold;"><h5>GROSS CONCESSIONS</h5></td>
                        <td style="text-align:right;font-weight:bold;"><h5>NET EARNINGS</h5></td>
                        <td style="text-align:right;font-weight:bold;"><h5>CHECK AMOUNT</h5></td>
                        <td style="text-align:right;font-weight:bold;"><h5>1099 EARNINGS</h5></td>';

                
            $html.='</tr>';
    
    
            if(!empty($get_trans_data))
            {
                    $total_comm_received=0;
                    $total_comm_paid=0;
                    $total_inv=0;
                    $total_no_of_trans=0;

                    
                   
                        foreach($get_trans_data as $trans_key=>$row_item)
                        {

                            $is_branch =false;
                            $trans_rows= [];
                            if(isset($row_item['transactions'])) {
                                $is_branch =true;
                                 $html.= '<tr> <td colspan="6" align="left"> <span  style="font-weight:bold;" class="branch_name">** BRANCH : '.$row_item['branch_name'].'</span></td> </tr>';
                                $trans_rows = $row_item['transactions'];
                            }
                            else {
                                $trans_rows[] = $row_item;
                            }
                            
                            $sub_earning_1099= $sub_check_amount = $sub_net_comm = $sub_gross_earning = 0;
                            foreach($trans_rows as $trans_data ){

                                 $gross_earning=$trans_data['gross_production'] - $trans_data['commission_received'];
                                 $net_commission=$trans_data['commission_paid'] + $trans_data['split_paid'] + $trans_data['override_paid'];
                                 $check_amount=$trans_data['check_amount'] >= $trans_data['minimum_check_amount'] ? $trans_data['check_amount'] : 0.00;
                                 $earning_1099=$trans_data['commission_paid'] + $trans_data['split_paid'] + $trans_data['override_paid'] + $trans_data['taxable_adjustments']  - $trans_data['finra'] - $trans_data['sipc'];
                                 $total_gross_earning+=$gross_earning;
                                 $total_net_commission+=$net_commission;
                                 $total_check_amount+=$check_amount;
                                 $total_earning_1099+=$earning_1099; 
                                
                                 $sub_gross_earning+=$gross_earning;
                                 $sub_net_comm+=$net_commission;
                                 $sub_check_amount+=$check_amount;
                                 $sub_earning_1099+=$earning_1099; 
                           
                                $html.='<tr>';
                               
                                $html.=' <td style="font-size:8px;font-weight:normal;text-align:left;">'.$trans_data['broker_lastname'].', '.$trans_data['broker_firstname'].'</td>
                                       <td style="font-size:8px;font-weight:normal;text-align:left;">'.$trans_data['clearing_number'].'</td>
                                        <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($gross_earning,2).'</td>
                                       <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($net_commission,2).'</td>
                                       <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($check_amount,2).'</td>
                                        <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($earning_1099,2).'</td>';
                                $html.='</tr>';

                            }
                            if($is_branch) {
                                 $html.= '<tr style="background-color: #f1f1f1;">
                                 <td colspan="2" style="font-size:10px;font-weight:bold;text-align:right;" >*** BRANCH SUBTOTAL ***</td> 
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"> $'.number_format($sub_gross_earning,2).'</td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"> $'.number_format($sub_net_comm,2).'</td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;">$'.number_format($sub_check_amount,2).'</td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"> $'.number_format($sub_earning_1099,2).'</td>
                                 </tr>';

                             }
                            
                        }
                                 
                    
                    
                
                
                    $html.='<tr style="background-color: #f1f1f1;">
                                    <td  style="font-size:10px;font-weight:bold;text-align:right;"  colspan="2"><b>**REPORT TOTALS **</b></td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_gross_earning,2).'</b></td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_net_commission,2).'</b></td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_check_amount,2).'</b></td>
                                    <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_earning_1099,2).'</b></td>';
                   
                    $html.='</tr>';
                
                           
               
            }
            else
            {
                $html.='<tr>
                            <td style="font-size:8px;font-weight:cold;text-align:center;" colspan="6">No record found.</td>
                        </tr>';
            }           
            $html.='</table>';
            $pdf->writeHTML($html, false, 0, false, 0);
            $pdf->Ln(5);
            
           
            $pdf->lastPage();
            if(isset($_GET['open']) && $_GET['open'] == 'output_print')
            {
                $pdf->IncludeJS("print();");
            }
            $pdf->Output('transaction_report_batch.pdf', 'I');
            
            exit;
    }
    else if($report_for == 'broker_ranking') {
                 //print_r($prod_cat);
            
                $prod_cat =array_filter($prod_cat,function($value) {
                    return $value > 0;
                });
                // print_r($prod_cat);

                $is_all_category = empty($prod_cat);

                $ranks = ['Total Earnings','Gross Concessions','Total Sales','Profitability'];
               // var_dump($report_rank_order_by);
                $subheading = '<h6> <strong> BROKER RANKINGS REPORT </strong> </h6> <h5> '.$companyhead.' </h5>  <h6>  ';
                

                if(!empty($prod_cat)) {
                    $selected_pro_categories = $instance_trans->select_category($prod_cat);
                    if(!empty($selected_pro_categories)) {
                        $cat_names = array_column($selected_pro_categories, 'type');
                        $subheading .= implode(', ', $cat_names); 
                    }
                }
                else $subheading.= 'All Categories';
                $subheading.= '</h6> <h6>';
                $subheading.=$ranks[$report_rank_order_by-1].', ';
                $subheading.= ($broker_type == 1) ? ' All Brokers' :  'Top '.$top_broker_count.' Brokers'; 
                $subheading.= '</h6>';

                $limit = ($broker_type == 2) ? $top_broker_count : 0; 
                $earning_filter = compact('earning_by','beginning_date','ending_date');
                $get_trans_data = $instance_trans->select_broker_ranking_sale_report($prod_cat,$company,$report_rank_order_by,$limit,$earning_filter);
               
              
               
                $subheading2 = '';
                if($earning_by == 2) {
                    $subheading2="Date Received ".$beginning_date." through ".$ending_date;
                }

                $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // add a page
                $pdf->AddPage('P');
                // Title
                $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="40px" />';
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);

                $html='<table border="0" width="100%">
                            <tr>';
                             $html .='<td width="20%" align="left">'.date("m/d/Y").'</td>';
                            
                            $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">'. $img.'<br/>
                          '.$subheading.'
                        
                          <h6>'.$subheading2.'</h6></td>';
                                             
                                $html.='<td width="20%" align="right">Page 1</td>';
                            
                            $html.='</tr>
                    </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);
                $html='<table border="0">
                            <tr>';
                                $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;"></td>';
                        $html .='</tr>
                        </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);

                /*$html= '<table border="0" width="100%">
                        <tr>
                             <td width="20%" align="left">'.date("m/d/Y").'</td>
                             <td width="60%" align="center">'. $img.'<br/>
                              <strong><h9>'.$companyhead.'</h9>
                              <br/>'.$subheading.'<br/> 
                              <h9>'.$subheading2.'<br/>'. $subheading1.'</h9></strong> </td>
                             <td width="20%" align="right">Page 1</td>
                        </tr>        
                </table>  <br />';*/
                $html= '<table border="0" cellpadding="1" width="100%">
                    <thead class="modal-heading-row">
                    <tr style="background-color: #f1f1f1;">';
                         if(!$is_all_category) $html.='<td style="text-align:left;font-weight:bold;"> &nbsp; </td>';
                        $html.='<td style="text-align:left;font-weight:bold;">Broker </td>
                        <td style="text-align:right;font-weight:bold;">INTERNAL NO.</td>
                        <td style="text-align:right;font-weight:bold;">'; $html.= 'TOTAL'; $html.= '  <br> EARNINGS </td>
                        <td style="text-align:right;font-weight:bold;">'; $html.= 'GROSS'; $html.= ' CONCESSIONS </td>
                        <td style="text-align:right;font-weight:bold;">'; $html.= 'TOTAL'; $html.= ' SALES </td>
                        <td style="text-align:right;font-weight:bold;">'; $html.= 'TOTAL'; $html.= ' PROFITABILITY </td>
                    </tr>
                    </thead>';


                if(!empty($get_trans_data)) {
                    $total_profit = $total_concessions = $total_invest = $total_comm = 0;

                    $html.= '<tbody>';
                    foreach($get_trans_data as $key => $trans_row) {
                        $total_comm+= $trans_row['total_earnings'];
                        $total_invest += $trans_row['total_investment'];
                        $total_concessions += $trans_row['total_concessions'];
                        $total_profit += $trans_row['total_profit'];
                        $html.= '<tr>';
                      if(!$is_all_category)   $html.= '<td style="font-size:8px;font-weight:normal;">'.($key+1).' </td>';
                        $html.= '<td style="font-size:8px;font-weight:normal;"> '.$trans_row['broker_fullname'].'</td>';
                        $html.= '<td style="font-size:8px;font-weight:normal;" align="right">'.$trans_row['internal_id'].'</td>';
                        $html.= '<td style="font-size:8px;font-weight:normal;" align="right"> '.number_format($trans_row['total_earnings'],2).' </td>';
                        $html.= '<td style="font-size:8px;font-weight:normal;" align="right"> '.number_format($trans_row['total_investment'],2).' </td>';
                        $html.= '<td style="font-size:8px;font-weight:normal;" align="right">'.number_format($trans_row['total_concessions'],2).'</td>';
                        $html.= '<td style="font-size:8px;font-weight:normal;" align="right">'.number_format($trans_row['total_profit'],2).' </td>';
                        $html.= '</tr>';
                    }
                    $html.= '</tbody>';
                    $html.= '<tfoot> <tr style="background-color: #f1f1f1;" class="t-footer-items">';
                       
                          if(!$is_all_category)  $html.=  '<td >&nbsp;</td>';
                            $html.= '<td >&nbsp;</td>';
                  
                        $html.= '<td style="font-size:10px;font-weight:bold;text-align:right;"> *** REPORT TOTALS ***</td>';
                        $html.= '<td style="font-size:10px;font-weight:bold;text-align:right;" align="right"> '.number_format($total_comm,2).' </td>';
                        $html.= '<td style="font-size:10px;font-weight:bold;text-align:right;" align="right"> '.number_format($total_invest,2).' </td>';
                        $html.= '<td style="font-size:10px;font-weight:bold;text-align:right;" align="right">'.number_format($total_concessions,2).'</td>';
                         $html.= '<td style="font-size:10px;font-weight:bold;text-align:right;" align="right">'.number_format($total_profit,2).'</td>';
                    $html.= '</tr> </tfoot>';
                }
                $html.= '</table>';
               // echo $html; die;
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(5);
                
               
                $pdf->lastPage();
                if(isset($_GET['open']) && $_GET['open'] == 'output_print')
                {
                    $pdf->IncludeJS("print();");
                }
                $pdf->Output('transaction_report_batch.pdf', 'I');
                
                exit;





    }
    else if($report_for == 'annual_broker_report') {
        $date_heading = ($annul_broker_date_type == 1) ? 'By Trade Date' : 'By Settlement Date';

        $subheading = 'ANNUAL BROKER REPORT ';
        $subheading2 = 'For '.$report_year.' - '.$date_heading;
        $rows = $instance_trans->select_annual_broker_report($report_year,$is_trail,$broker,$company,$annul_broker_date_type);
        

         if($broker > 0){
                $branch_instance = new broker_master();

                $name  = $branch_instance->select_broker_by_id($broker);
               
                $companyhead.=", ".ucfirst($name['last_name']).' '.ucfirst($name['first_name']);
          
            }
            else {
                $companyhead.=', All Brokers';
            }

                //  print_r($rows);
            $date_heading = ($annul_broker_date_type == 1) ? 'By Trade Date' : 'By Settlement Date';
            $subheading = 'Annual Broker Report ';
            $subheading2 = 'For '.$report_year.' - '.$date_heading;
            $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // add a page
                $pdf->AddPage('L');
                // Title
                $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="40px" />';
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);

                $html='<table border="0" width="100%">
                            <tr>';
                             $html .='<td width="20%" align="left">'.date("m/d/Y").'</td>';
                            
                            $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">'.$img.'<br/><strong><h9>'.$subheading.'<br/>'.$companyhead.'<br/>'.$subheading2.'<br/>'.$subheading1.'</h9></strong></td>';
                                             
                                $html.='<td width="20%" align="right">Page 1</td>';
                            
                            $html.='</tr>
                    </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);
                $html='<table border="0">
                            <tr>';
                                $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;"></td>';
                        $html .='</tr>
                        </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);    
              /*echo '<table border="0" width="100%">
                    <tr>
                         <td width="20%" align="left">'.date("m/d/Y").'</td>
                         <td width="60%" align="center">'. $img.'<br/>
                          <strong><h9>'.$companyhead.'</h9>
                          <br/>'.$subheading.'<br/> 
                          <h9>'.$subheading2.'<br/>'. $subheading1.'</h9></strong> </td>
                         <td width="20%" align="right">Page 1</td>
                    </tr>        
            </table>  <br />';*/
             $html= '<table border="0" cellpadding="1" width="100%">
                <thead class="modal-heading-row">
                <tr style="background-color: #f1f1f1;"> 
                    <td align="right" >MONTH </td>
                    <td align="right" >NO. OF TRADES </td>
                    <td align="right" >GROSS CONCESSIONS </td>
                    <td align="right" >NET COMMISSION </td>
                   
                </tr>
                </thead>';
            $html.= '<tbody>';
            $total_trades = $gross = $total_comm = 0;
            $notFoundRow = ['no_of_trades'=>0,'gross_conession'=>0,'commission_received'=>0];
            for($i= 1 ;$i <=12 ; $i++) {
                $get_month_transaction = isset($rows[$i]) ? $rows[$i] : $notFoundRow; 
                $dateObj   = DateTime::createFromFormat('!m', $i);
                $total_comm+= $get_month_transaction['commission_received'];
                $gross+= $get_month_transaction['gross_conession'];
                $total_trades+= $get_month_transaction['no_of_trades'];
                $html.= '<tr>';
                $html.= '<td align="right">'.$dateObj->format('F').' </td>';
                $html.= '<td align="right">'.$get_month_transaction['no_of_trades'].'</td> ';
                $html.= '<td align="right">'.number_format($get_month_transaction['gross_conession'],2).'</td> ';
                $html.= '<td align="right">'.number_format($get_month_transaction['commission_received'],2).'</td> ';
                $html.= '</tr>';
            }
            $html.= '</tbody>';
            $html.= '<tfoot>';
            $html.= '<tr class="t-footer-items" style="background-color: #f1f1f1;">';
            $html.= '<td align="right" style="font-weight:bold;" > *** REPORT TOTALS ***</td>';
            $html.= '<td align="right" style="font-weight:bold;">'.$total_trades.'</td> ';
            $html.= '<td align="right" style="font-weight:bold;">'.number_format($gross,2).'</td> ';
            $html.= '<td align="right" style="font-weight:bold;">'.number_format($total_comm,2).'</td> ';
            $html.= '</tr>';
            $html.= '</tfoot>';
            $html.= '</table>';


                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(5);
                
               
                $pdf->lastPage();
                if(isset($_GET['open']) && $_GET['open'] == 'output_print')
                {
                    $pdf->IncludeJS("print();");
                }
                $pdf->Output('transaction_report_batch.pdf', 'I');
                
                exit;
    }
    else if($report_for == 'monthly_broker_production') {
        $subheading = 'MONTHLY BROKER PRODUCTION REPORT ';
        $subheading2 = 'Trade Dates: '.$beginning_date." - ".$ending_date;
         $earning_by = 2;
            $earning_filter = compact('earning_by','beginning_date','ending_date');
            $rows = $instance_trans->select_monthly_broker_production_report($company,$earning_filter);
         //  echo '<pre>'; print_r($rows); echo '</pre>';
            $subheading = 'MONTHLY BROKER PRODUCTION REPORT ';
            $subheading2 = 'Trade Dates: '.$beginning_date." - ".$ending_date;
            /*echo '<table border="0" width="100%">
                    <tr>
                         <td width="20%" align="left">'.date("m/d/Y").'</td>
                         <td width="60%" align="center">'. $img.'<br/>
                          <strong><h9>'.$companyhead.'</h9>
                          <br/>'.$subheading.'<br/> 
                          <h9>'.$subheading2.'<br/>'. $subheading1.'</h9></strong> </td>
                         <td width="20%" align="right">Page 1</td>
                    </tr>        
            </table>  <br />';*/

            $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // add a page
                $pdf->AddPage('L');
                // Title
                $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="40px" />';
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);

                $html='<table border="0" width="100%">
                            <tr>';
                             $html .='<td width="20%" align="left">'.date("m/d/Y").'</td>';
                            
                            $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">'.$img.'<br/><strong><h9>'.$subheading.'<br>'.$companyhead.'<br/>'.$subheading2.'<br/>'.$subheading1.'</h9></strong></td>';
                                             
                                $html.='<td width="20%" align="right">Page 1</td>';
                            
                            $html.='</tr>
                    </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);
                $html='<table border="0">
                            <tr>';
                                $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;"></td>';
                        $html .='</tr>
                        </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10); 
               $html= '<table border="0" cellpadding="1" width="100%">
                <thead class="modal-heading-row">
                 <tr style="vertical-align: bottom;background-color: #f1f1f1;">
                    <td align="right">PRODUCT CATEGORY </td>
                    <td align="right">INVESTMENT AMOUNT </td>
                    <td align="right">COMMISSION RECEIVED </td>
                    <td align="right">NET COMMISSION </td>
                </tr>
                </thead>';
            if(!empty($rows)) {
                $html.=  '<tbody>';
                $main_net_commission = $main_invest_total = $main_commision_received = 0;

                foreach($rows as $trans_items) {
                   $net_commission = $comm_rev = $total_inv = 0;
                     $html.= '<tr>
                        <td  align="left"> <span class="broker_name" style="text-decoration:underline;font-weight:bold;"> '.$trans_items['broker_full_name'].' &nbsp;&nbsp;&nbsp; 
                            <span class="broker_id">#'.$trans_items['internal_id'].'</span>
                            </span>
                        </td>
                        <td colspan="3"> </td>
                    </tr>';
                    foreach($trans_items['transactions'] as $sub_items) {
                        $total_inv+= $sub_items['total_investment'];
                        $comm_rev+= $sub_items['total_commission_received'];
                        $net_commission+= $sub_items['net_commission'];

                        $main_invest_total+= $sub_items['total_investment'];
                        $main_commision_received+= $sub_items['total_commission_received'];
                        $main_net_commission+= $sub_items['net_commission'];

                         $html.= '<tr class="sub-items">
                        <td align="right">'.$sub_items['product_cat_name'].'</td>
                        <td align="right">'.number_format($sub_items['total_investment'],2).' </td>
                        <td align="right"> '.number_format($sub_items['total_commission_received'],2).'</td>
                        <td align="right"> '.number_format($sub_items['net_commission'],2).'</td>
                        </tr>';
                    }
                     $html.= '<tr class="t-footer-items" style="background-color: #f1f1f1;"> 
                            <td align="right"> *** BROKER SUBTOTAL ***</td>
                            <td align="right" ><span> '.number_format($total_inv,2).'</span></td>
                            <td align="right" ><span>'.number_format($comm_rev,2).'</span></td>
                            <td align="right" ><span>'.number_format($net_commission,2).'</span></td>
                        </tr>';
                }
                 $html.= '</tbody>';

                $html.= '<tr class="t-footer-items" style="background-color: #f1f1f1;"> 
                            <td align="right"> *** REPORT TOTALS ***</td>
                            <td align="right" ><span> '.number_format($main_invest_total,2).'</span></td>
                            <td align="right" ><span>'.number_format($main_commision_received,2).'</span></td>
                            <td align="right" ><span>'.number_format($main_net_commission,2).'</span></td>
                        </tr>';
            }
             $html.= '</table>'; 
             $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(5);
                
               
                $pdf->lastPage();
                if(isset($_GET['open']) && $_GET['open'] == 'output_print')
                {
                    $pdf->IncludeJS("print();");
                }
                $pdf->Output('transaction_report_batch.pdf', 'I');
                
                exit;
    }
    else if($report_for == 'monthly_branch_office') {
        $subheading = 'MONTHLY BRANCH OFFICE PRODUCTION REPORT';
        $subheading2 = 'Ending Date: '.date('F d, Y',strtotime($ending_date));
        $subheading = 'MONTHLY BRANCH OFFICE PRODUCTION REPORT';
            $subheading2 = 'Ending Date: '.date('F d, Y',strtotime($ending_date));
            $rows = $instance_trans->select_monthly_branch_office_report($company,$branch,$ending_date);

             $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // add a page
                $pdf->AddPage('L');
                // Title
                $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="40px" />';
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);

                $html='<table border="0" width="100%">
                            <tr>';
                             $html .='<td width="20%" align="left">'.date("m/d/Y").'</td>';
                            
                            $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">'.$img.'<br/><strong><h9>'.$subheading.'<br/>'.$companyhead.'<br/>'.$subheading2.'<br/>'.$subheading1.'</h9></strong></td>';
                                             
                                $html.='<td width="20%" align="right">Page 1</td>';
                            
                            $html.='</tr>
                    </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10);
                $html='<table border="0">
                            <tr>';
                                $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;"></td>';
                        $html .='</tr>
                        </table>';
                $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(2);
                
                $pdf->SetFont('times','B',12);
                $pdf->SetFont('times','',10); 
            //echo '<pre>'; print_r($rows); echo '</pre>';
            /*echo '<table border="0" width="100%">
                    <tr>
                         <td width="20%" align="left">'.date("m/d/Y").'</td>
                         <td width="60%" align="center">'. $img.'<br/>
                          <strong><h9>'.$companyhead.'</h9>
                          <br/>'.$subheading.'<br/> 
                          <h9>'.$subheading2.'<br/>'. $subheading1.'</h9></strong> </td>
                         <td width="20%" align="right">Page 1</td>
                    </tr>        
            </table>  <br />';*/
            $html= '<table border="0" cellpadding="1" width="100%">
            <thead class="modal-heading-row">
            <tr style="vertical-align: bottom;background-color: #f1f1f1;">
                    <td align="right">PRODUCT CATEGORY  </td>
                    <td align="right">INVESTMENT AMOUNT </td>
                    <td align="right">COMMISSION RECEIVED </td>
                    <td align="right">NET COMMISSION </td>
                </tr>
                </thead>';
            if(!empty($rows)) {
                $main_net_commission = $main_total_concessions = $main_invest_total = 0;
                $html.= '<tbody>';
                foreach($rows as $trans_items) {
                  $branch_name = $trans_items['branch_name'] ?? 'Misc (Branch Name not available)';
                    $html.= '<tr>
                       <td class="td-branch"   colspan="4" align="center"> <span class="branch_name"
                       style="text-decoration:underline;font-weight:bold;"> '.$branch_name.' 
                            </span>
                        </td>
                    </tr>';
                    foreach($trans_items['brokers'] as $broker_id => $sub_items) {
                      $net_commission=   $comm_rev = $total_inv = 0;
                       $html.= '<tr>
                        <td  align="left"> <span class="broker_name" style="font-weight:bold;"> '.$sub_items['broker_full_name'].' &nbsp;&nbsp;&nbsp; 
                            <span class="broker_id">#'.$sub_items['internal_id'].'</span>
                            </span>
                        </td>
                        <td colspan="3"> </td>
                    </tr>';

                        
                        foreach($sub_items['transactions'] as $sub_items) {
                            $total_inv+= $sub_items['total_investment'];
                            $comm_rev+= $sub_items['total_concessions'];
                            $net_commission+=$sub_items['net_commission'];

                            $main_invest_total+= $sub_items['total_investment'];
                            $main_total_concessions+= $sub_items['total_commission_received'];
                            $main_net_commission+= $sub_items['net_commission'];

                            $html.= '<tr class="sub-items">
                            <td align="right">'.$sub_items['product_cat_name'].'</td>
                            <td align="right">'.number_format($sub_items['total_investment'],2).' </td>
                            <td align="right"> '.number_format($sub_items['total_commission_received'],2).'</td>
                            <td align="right"> '.number_format($sub_items['net_commission'],2).'</td>
                            </tr>';
                        }

                       $html.= '<tr class="t-footer-items" style="background-color: #f1f1f1;"> 
                            <td align="right"> *** BROKER SUBTOTAL ***</td>
                            <td align="right" ><span> '.number_format($total_inv,2).'</span></td>
                            <td align="right" ><span>'.number_format($comm_rev,2).'</span></td>
                            <td align="right" ><span>'.number_format($net_commission,2).'</span></td>
                        </tr>';
                    }
                }
                $html.= '</tbody>';

                $html.= '<tr class="t-footer-items" style="background-color: #f1f1f1;"> 
                            <td align="right"> *** REPORT TOTALS ***</td>
                            <td align="right" ><span> '.number_format($main_invest_total,2).'</span></td>
                            <td align="right" ><span>'.number_format($main_commision_received,2).'</span></td>
                            <td align="right" ><span>'.number_format($main_net_commission,2).'</span></td>
                        </tr>';

            }

            $html.= '</table>';
            //echo $html; die;
            $pdf->writeHTML($html, false, 0, false, 0);
                $pdf->Ln(5);
                
               
                $pdf->lastPage();
                if(isset($_GET['open']) && $_GET['open'] == 'output_print')
                {
                    $pdf->IncludeJS("print();");
                }
                $pdf->Output('transaction_report_batch.pdf', 'I');
                
                exit;
    }
    else{    
       
            $get_trans_data = $instance_trans->select_transcation_history_report_v2($report_for,$sort_by,$branch,$broker,'',$client,$product,$beginning_date,$ending_date,$batch,$date_by,$filter_by,$is_trail,$prod_cat);
           if(!empty($get_trans_data))
            {
                $get_data_by_category = array();
                foreach($get_trans_data as $key=>$val)
                {
                    $get_data_by_category[$val['product_category_name']][] = $val;
                }
                $get_trans_data = $get_data_by_category;
            }
    

    // create new PDF document
    $pdf = new RRPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // add a page
    $pdf->AddPage('L');
    // Title
    $img = '<img src="'.SITE_URL."upload/logo/".$system_logo.'" height="40px" />';
    
    $pdf->SetFont('times','B',12);
    $pdf->SetFont('times','',10);

    $html='<table border="0" width="100%">
                <tr>';
                 $html .='<td width="20%" align="left">'.date("m/d/Y").'</td>';
                
                $html .='<td width="60%" style="font-size:14px;font-weight:bold;text-align:center;">'.$img.'<br/><strong><h9>'.$companyhead.'<br/>'.$subheading.'<br/>'.$subheading2.'<br/>'.$subheading1.'</h9></strong></td>';
                                 
                    $html.='<td width="20%" align="right">Page 1</td>';
                
                $html.='</tr>
        </table>';
    $pdf->writeHTML($html, false, 0, false, 0);
    $pdf->Ln(2);
    
    $pdf->SetFont('times','B',12);
    $pdf->SetFont('times','',10);
    $html='<table border="0">
                <tr>';
                    $html .='<td width="100%" style="font-size:12px;font-weight:bold;text-align:center;"></td>';
            $html .='</tr>
            </table>';
    $pdf->writeHTML($html, false, 0, false, 0);
    $pdf->Ln(2);
    
    $pdf->SetFont('times','B',12);
    $pdf->SetFont('times','',10);
    $html='<table border="0" cellpadding="1" width="100%">
                <tr style="background-color: #f1f1f1;">';
                 if($report_for == "Production by Sponsor Report") 
                        {$html.='<td style="text-align:left;font-weight:bold;"><h5>SPONSER </h5></td>';}
                else 
                        {$html.='<td style="text-align:left;font-weight:bold;"><h5>PRODUCT </h5></td>';}
                 
                $html.='
                   <td style="text-align:right;font-weight:bold;"><h5>AMOUNT INVESTED</h5></td>
                        <td style="text-align:right;font-weight:bold;"><h5>COMMISSION RECEIVED</h5></td>
                        <td style="text-align:right;font-weight:bold;"><h5>COMMISSION PAID</h5></td>';
                if($report_for == "Category Summary Report") {
                    $html.='<td style="text-align:right;font-weight:bold;"><h5>#TRANS </h5></td>
                        <td style="text-align:right;font-weight:bold;"><h5>%TOTAL</h5></td>';
                }
                
    $html.='</tr>';
    
    
    if(!empty($get_trans_data))
    {
            $total_comm_received=0;
            $total_comm_paid=0;
            $total_inv=0;
            $total_no_of_trans=0;
            foreach($get_trans_data as $key => $category_data)
            {
                foreach($category_data as $trans_key=>$trans_data)
                {
                    $total_comm_received+=$trans_data['commission_received'];
                    $total_comm_paid+=$trans_data['charge_amount'];
                    $total_inv+=$trans_data['invest_amount'];
                    $total_no_of_trans+=1;
                }
            }
            //echo '<pre>';print_r($get_trans_data);
            foreach($get_trans_data as $key => $category_data)
            {
                if($report_for == "Production by Product Category"){               
                    $html.= ' <tr>
                                    <td colspan="3" style="font-size:12px;font-weight:bold;text-align:left;">'.$key.'</td>
                                </tr>' ;               
                             
                }
                $total_comm_received_cat=0;
                $total_comm_paid_cat=0;
                $total_inv_cat=0;
                $total_no_of_trans_cat=0;
                $cat_percentage=0;
                foreach($category_data as $trans_key=>$trans_data)
                {
                        //echo '<pre>';print_r($category_data);
                        // $total_comm_received+=$trans_data['commission_received'];
                        // $total_comm_paid+=$trans_data['charge_amount'];
                        // $total_inv+=$trans_data['invest_amount'];
                        $total_comm_received_cat+=$trans_data['commission_received'];
                        $total_comm_paid_cat+=$trans_data['charge_amount'];
                        $total_inv_cat+=$trans_data['invest_amount'];
                        $total_no_of_trans_cat+=1;
                
                    if($report_for != "Category Summary Report")
                    {
                        $html.='<tr>';
                        if($report_for == "Production by Sponsor Report") {
                             $html.='<td style="font-size:10px;font-weight:normal;text-align:left;">'.$trans_data['sponsor_name'].'</td>';}
                        else{               

                             $html.='<td style="font-size:10px;font-weight:normal;text-align:left;">'.$trans_data['product_name'].'</td>';
                        }
                        $html.='
                               <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($trans_data['invest_amount'],2).'</td>
                               <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($trans_data['commission_received'],2).'</td>
                               <td style="font-size:8px;font-weight:normal;text-align:right;">$'.number_format($trans_data['charge_amount'],2).'</td>';
                        $html.='</tr>';
                    }
                }
            if($report_for == "Category Summary Report") 
            {
                $html.=' <tr >
                                    <td style="font-size:10px;font-weight:normal;text-align:left;">'.$key.'</td>
                                    <td style="font-size:10px;font-weight:normal;text-align:right;">$'.number_format($total_inv_cat,2).'</td>
                                    <td style="font-size:10px;font-weight:normal;text-align:right;">$'.number_format($total_comm_received_cat,2).'</td>
                                    <td style="font-size:10px;font-weight:normal;text-align:right;">$'.number_format($total_comm_paid_cat,2).'</td>
                                    <td style="font-size:10px;font-weight:normal;text-align:right;">'.number_format($total_no_of_trans_cat,0).'</td>
                                    <td style="font-size:10px;font-weight:normal;text-align:right;">'.number_format($total_no_of_trans_cat*100/$total_no_of_trans,2).'%</td>
                                    </tr>';
            }                
            else if($report_for == "Production by Product Category") 
            { 
             $html.='<tr style="background-color: #f1f1f1;">
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>*** PRODUCT CATEGORY SUBTOTALS ***</b></td>
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_inv_cat,2).'</b></td>
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_comm_received_cat,2).'</b></td>
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_comm_paid_cat,2).'</b></td></tr>';
            }
            
        
        }
            $html.='<tr style="background-color: #f1f1f1;">
                            <td  style="font-size:10px;font-weight:bold;text-align:right;"><b>**REPORT TOTAL **</b></td>
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_inv,2).'</b></td>
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_comm_received,2).'</b></td>
                            <td style="font-size:10px;font-weight:bold;text-align:right;"><b>$'.number_format($total_comm_paid,2).'</b></td>';
            if($report_for == "Category Summary Report") {
                $html.='<td style="font-size:10px;font-weight:bold;text-align:right;"><b>'.number_format($total_no_of_trans,0).'</b></td>
                        <td style="font-size:10px;font-weight:bold;text-align:right;"><b></b></td>';
            }
            $html.='</tr>';
        
                   
       
    }
    else
    {
        $html.='<tr>
                    <td style="font-size:8px;font-weight:cold;text-align:center;" colspan="8">No record found.</td>
                </tr>';
    }           
    $html.='</table>';
    $pdf->writeHTML($html, false, 0, false, 0);
    $pdf->Ln(5);
    
   
    $pdf->lastPage();
    if(isset($_GET['open']) && $_GET['open'] == 'output_print')
    {
        $pdf->IncludeJS("print();");
    }
    $pdf->Output('transaction_report_batch.pdf', 'I');
    
    exit;
}
?>