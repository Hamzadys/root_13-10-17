<?php
$page_security = 'SS_DASHBOARD';
$path_to_root="../../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/modules/dashboard/charts/charts_utils.php");

$created_by = $_SESSION["wa_current_user"]->user;

	$begin = begin_fiscalyear();
	$today = Today();
	$begin1 = date2sql($begin);
	$today1 = date2sql($today);
	$sql = "SELECT SUM((ov_amount + ov_discount) * rate*IF(trans.type = ".ST_CUSTCREDIT.", -1, 1)) AS total,d.debtor_no, d.name FROM
		".TB_PREF."debtor_trans AS trans, ".TB_PREF."debtors_master AS d WHERE trans.debtor_no=d.debtor_no
		AND (trans.type = ".ST_SALESINVOICE." OR trans.type = ".ST_CUSTCREDIT.")
		AND tran_date >= '$begin1' AND tran_date <= '$today1' GROUP by d.debtor_no ORDER BY total DESC, d.debtor_no 
		LIMIT 10";
	$result = db_query($sql);
	
	
	$chart_type = 'BarChart';
	
	if (isset($_GET['chart_type'])) {
		$chart_type = $_GET['chart_type'];
	} if (isset($_POST['chart_type'])) {
		$chart_type = $_POST['chart_type'];
	}
	
	echo '<link rel="stylesheet" type="text/css" href="' . $path_to_root . '/themes/'.user_theme(). '/widget-styles.css" />';
?>   

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:['table','corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '<?php echo _("Customer"); ?>');
        data.addColumn('number', '<?php echo _("Amount"); ?>');
        data.addRows(<?php echo db_num_rows($result); ?>);
        
                      <?php 
                          $i = 0;        		
                          while ($myrow = db_fetch($result))
                  		{ 
                  			?>
								data.setValue(<?php echo $i; ?>,0,'<?php echo $myrow['name']; ?>');									
								<?php
								if ($chart_type == 'PieChart') {						    	
						    	?>							    	
						    		data.setValue(<?php echo $i; ?>,1,<?php echo abs($myrow['total']); ?>);
	                  	        <?php } else { ?>	
	                  	      		data.setValue(<?php echo $i; ?>,1,<?php echo $myrow['total']; ?>);
	                  	        <?php
	                  	        }    
                  		    $i++;
                  		}
                  	?>

        var formatter = new google.visualization.NumberFormat();
        formatter.format(data, 1);

        var table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, null);

        <?php if ($chart_type == 'Table') { ?>
	        var table = new google.visualization.Table(document.getElementById('chart_div'));
	        table.draw(data, null);
	    <?php } else { ?>    
        
	        var chart = new google.visualization.<?php echo $chart_type; ?>(document.getElementById('chart_div'));
	        
	        <?php if ($chart_type == 'BarChart') { ?>
			        
			        chart.draw(data, {title: '<?php echo _("Top 10 customers in fiscal year"); ?>',
			        				  chartArea:{left:100},     
			        				  is3D: true,   				  
			                          vAxis: {title: '<?php echo _("Customer"); ?>', titleTextStyle: {color: 'red'}},
			                          hAxis: {title: '<?php echo _("Amount"); ?>', format:'#,###', titleTextStyle: {color: 'blue'}}
			                         });
	        <?php } else { ?>       
	
			        chart.draw(data, {title: '<?php echo _("Top 10 customers in fiscal year"); ?>',
			        				  chartArea:{left:100},
			        				  is3D: true,        				  
			                          hAxis: {title: '<?php echo _("Customer"); ?>', titleTextStyle: {color: 'red'}},
			                          vAxis: {title: '<?php echo _("Amount"); ?>', format:'#,###', titleTextStyle: {color: 'blue'}}
			                         });
	        <?php } ?> 
	    <?php } ?>  
      }

      function setOption(param) {           
    	  document.FormSwitch.chart_type.value = param;
          document.FormSwitch.submit();
        }
      
    </script>
    
    <form name="FormSwitch" action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
		<div align="right"><span class="headingtext"><?php echo _("View By"); ?>:&nbsp;</span>
			  <?php 
			  
			  $_arrchartType = array("AreaChart" => _("Area Chart"),
			  						 "BarChart" => _("Bar Chart"),	
			   				   		 "ColumnChart" => _("Column Chart"),
			  						 "LineChart" => _("Line Chart"),
			  						 "PieChart" => _("Pie Chart"));
			  
			  echo getComboxBoxOnChangeEvent($_arrchartType, 'chart_type', '1', 'combo', 'setOption(this.options[this.selectedIndex].value);', trim($chart_type)) ?>
			  &nbsp;&nbsp;	  
		</div> 
	</form>
 	
 	<div id="table_div"  style="width: 550px; height: 260px;"></div>
    <div id="chart_div"  style="width: 550px; height: 300px;"></div>