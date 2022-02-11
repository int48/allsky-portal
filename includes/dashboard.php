<?php

/**
*
*
*/
function DisplayDashboard($interface) {

	$status = new StatusMessages();

	$interface_output = get_interface_status("ifconfig $interface; iwconfig $interface");

	// $interface_output is sent and the other variables are returned.
	parse_ifconfig($interface_output, $strHWAddress, $strIPAddress, $strNetMask, $strRxPackets, $strTxPackets, $strRxBytes, $strTxBytes);

	// parse the iwconfig data:
	preg_match( '/ESSID:\"([a-zA-Z0-9\s]+)\"/i',$interface_output,$result );
	$strSSID = isset($result[1]) ?  str_replace('"','',$result[1]) : "[not set]";

	preg_match( '/Access Point: ([0-9a-f:]+)/i',$interface_output,$result );
	$strBSSID = isset($result[1]) ?  $result[1] : "[not set]";

	preg_match( '/Bit Rate=([0-9\.]+ Mb\/s)/i',$interface_output,$result );
	$strBitrate = isset($result[1]) ?  $result[1] : "[not set]";

	preg_match( '/Tx-Power=([0-9]+ dBm)/i',$interface_output,$result );
	$strTxPower = isset($result[1]) ?  $result[1] : "[not set]";

	preg_match( '/Link Quality=([0-9]+)/i',$interface_output,$result );
	$strLinkQuality = isset($result[1]) ?  $result[1] : "[not set]";

	preg_match( '/Signal level=(-?[0-9]+ dBm)/i',$interface_output,$result );
	$strSignalLevel = isset($result[1]) ?  $result[1] : "[not set]";

	preg_match('/Frequency:(\d+.\d+ GHz)/i',$interface_output,$result);
	$strFrequency = isset($result[1]) ?  $result[1] : "[not set]";

	// $interface and $interface_output are sent, $status is returned.
	$interface_up = handle_interface_POST_and_status($interface, $interface_output, $status);
?>

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-primary">
			<div class="panel-heading"><i class="fa fa-tachometer-alt fa-fw"></i> Dashboard   </div>
			<div class="panel-body">
				<p><?php $status->showMessages(); ?></p>
				<div class="row">
					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-body">
								<h4>Interface Information</h4>
								<div class="info-item">Interface Name</div> <?php echo $interface ?></br>
								<div class="info-item">IP Address</div>     <?php echo $strIPAddress ?></br>
								<div class="info-item">Subnet Mask</div>    <?php echo $strNetMask ?></br>
								<div class="info-item">Mac Address</div>    <?php echo $strHWAddress ?></br></br>

								<h4>Interface Statistics</h4>
								<div class="info-item">Received Packets</div>    <?php echo $strRxPackets ?></br>
								<div class="info-item">Received Bytes</div>      <?php echo $strRxBytes ?></br></br>
								<div class="info-item">Transferred Packets</div> <?php echo $strTxPackets ?></br>
								<div class="info-item">Transferred Bytes</div>   <?php echo $strTxBytes ?></br>
							</div><!-- /.panel-body -->
						</div><!-- /.panel-default -->
					</div><!-- /.col-md-6 -->

					<div class="col-md-6">
						<div class="panel panel-default">
							<div class="panel-body wireless">
								<h4>Wireless Information</h4>
								<div class="info-item">Connected To</div>   <?php echo $strSSID ?></br>
								<div class="info-item">AP Mac Address</div> <?php echo $strBSSID ?></br>
								<div class="info-item">Bitrate</div>        <?php echo $strBitrate ?></br>
								<div class="info-item">Signal Level</div>   <?php echo $strSignalLevel ?></br>
								<div class="info-item">Transmit Power</div> <?php echo $strTxPower ?></br>
								<div class="info-item">Frequency</div>      <?php echo $strFrequency ?></br>
								<div class="info-item">Link Quality</div>
								<div class="progress">
									<div class="progress-bar progress-bar-info"
									role="progressbar"
									aria-valuenow="<?php echo $strLinkQuality ?>" aria-valuemin="0" aria-valuemax="100"
									style="width: <?php echo $strLinkQuality ?>%;"><?php echo $strLinkQuality ?>%
									</div>
								</div>
							</div><!-- /.panel-body -->
						</div><!-- /.panel-default -->
					</div><!-- /.col-md-6 -->
				</div><!-- /.row -->

				<div class="col-lg-12">
					<div class="row">
						<form action="?page=<?php echo $interface ?>" method="POST">
							<?php if ( !$interface_up ) {
								echo "<input type='submit' class='btn btn-success' value='Start $interface' name='turn_up' />";
							} else {
								echo "<input type='submit' class='btn btn-warning' value='Stop $interface' name='turn_down' />";
							}
							?>
							<input type="button" class="btn btn-outline btn-primary" value="Refresh" onclick="document.location.reload(true)" />
						</form>
					</div>
				</div>

			</div><!-- /.panel-body -->
			<div class="panel-footer">Information provided by ifconfig and iwconfig</div>
		</div><!-- /.panel-default -->
	</div><!-- /.col-lg-12 -->
</div><!-- /.row -->
<?php
}
?>
