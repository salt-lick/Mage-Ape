<?php
#########################
#  Mage-Ape
#    v0.6 
# by CrashCart
#
# Mage-Ape is an atempt at a tool for testing and diagnosing errors with Magento API calls
#

ob_implicit_flush(1);

if (!empty($_POST)) {
  # Fetch POST variables
  $inputurl = $_POST['website'];
  $user = $_POST['user'];
  $pass = $_POST['pass'];
  #$apimehod = $_POST['apimethod'];
} else {  
  #defaults for testing
  $inputurl = "www.theath.simple-helix.net";
  $user = "theath";
  $pass = "donttell";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Mage Ape</title>
	<link rel="stylesheet" href="includes/bootstrap.min.css">
	<script src"includes/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
	<div class="pull-right">
		<img src="Mage_ape1.png">
	</div>
	<div class="col-sm-11 col-md-6 col-md-offset-2">
		<h1><a href="http://taoexmachina.com/mage-ape">Mage Ape</a><small>  Magento API test</small></h1>
		<div class="row">Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl</div>
		<div class-"row">
			<form action="" method="POST">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">Site or wsdl:</div>
						<input type="text" class="form-control" name="website" value="<?php echo $inputurl; ?>">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-primary">Get Info</button>
						</span>
					</div>
					<div class="input-group">
						<div class="input-group-addon">Username:</div>
						<input type="text" class="form-control" name="user" value="<?php echo $user;?>">
						<div class="input-group-addon">Password:</div>
						<input type="password" class="form-control" name="pass" value="<?php echo $pass; ?>">
					</div>
				</div>
			</form>
		</div>
		<div class-"row">

<?php
if (!empty($_POST)) {
	#loading gif here and visable
	ob_flush();

	# Filter URL. 
	# If http is missing, add http, 
	# If wsdl isn't declared, add wsdl and path
	$url = filter_var($inputurl, FILTER_SANITIZE_URL);
	if (strpos($url, "http://") !== 0) {$url = "http://" . $url;}
	$urlparts = parse_url($url);
	if (strpos($url, "wsdl") !== (strlen($url)-4)) {
		$url = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";    
	}
	echo '<div class="alert alert-info" role="alert">Started tests using:<br/>' . $url . "</div>";
	ob_flush();
	
	$headers = get_headers($url);
	echo '<div class="alert alert-warning" role="alert">';
	foreach ($headers as $h) {
		if (strpos(strtolower($h), "http/") !== false) {
			echo $h."<br>";
		}
	}
	echo "</div>";
	ob_flush(); 

	# catch errors
	try {
		# setup SOAP client connection and login
		$client = new SoapClient($url);
		if (empty($user) || empty($pass)) {
			$session = $client->startSession();
			echo '<div class="alert alert-info" role="alert">Started session without auth.<br>';
			echo 'Session ID: ' . $session . '</div>';
		} else {
			$session = $client->login($user, $pass);
			echo '<div class="alert alert-success" role="alert">Login successful.<br>';
			echo 'Session ID: ' . $session . '</div>';
		}
		ob_flush();

		#try a few useful commands to gather data and show connection is working.
		$result = $client->magentoInfo($session);
		echo '<div class="alert alert-info" role="alert">';
		echo $result->magento_edition . " Magento version " . $result->magento_version;
		echo '</div>';
		ob_flush();

		$result = $client->storeList($session);
		echo '<div class="alert alert-info" role="alert">';
		foreach ($result as $a) {
			echo "Store ID: " . $a->store_id . " Name: " . $a->code . "<br>";
		} 
		echo '</div>';
		ob_flush();

		$result = $client->resources($session);
		echo '<div class="alert alert-info" role="alert"><pre>';
		var_dump($result);
		echo '</pre></div>';
		ob_flush();

#
# Calls that only require session id
#
# resources
# globalFaults
# resourceName
# storeList
# magentoInfo
# directoryCountryList
# customerGroupList
# catalogProductAttributeSetList
# catalogProductAttributeTypes
# catalogProductTypeList
# catalogProductLinkTypes
# catalogProductCustomOptionTypes
# catalogCategoryAttributeList
#
	}

	#Error handling
	catch(Exception $e) {
		echo '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
	}

}
#I was going to ob_flush() here one last time but this is so close to the end of the file anyway.
?>
		</div>
	</div>
</div>
</body></html>

