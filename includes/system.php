<?php
/**
 *
 * Find the version of the Raspberry Pi
 * Currently only used for the system information page but may useful elsewhere
 *
 */

function RPiVersion()
{
	// Lookup table from https://www.raspberrypi.org/documentation/hardware/raspberrypi/revision-codes/README.md
	// Last updated december 2020
	$revisions = array(
	'0002' => 'Model B Revision 1.0',
	'0003' => 'Model B Revision 1.0 + ECN0001',
	'0004' => 'Model B Revision 2.0 (256 MB)',
	'0005' => 'Model B Revision 2.0 (256 MB)',
	'0006' => 'Model B Revision 2.0 (256 MB)',
	'0007' => 'Model A',
	'0008' => 'Model A',
	'0009' => 'Model A',
	'000d' => 'Model B Revision 2.0 (512 MB)',
	'000e' => 'Model B Revision 2.0 (512 MB)',
	'000f' => 'Model B Revision 2.0 (512 MB)',
	'0010' => 'Model B+',
	'0013' => 'Model B+',
	'0011' => 'Compute Module',
	'0012' => 'Model A+',
	'a01040' => 'Pi 2 Model B Revision 1.0 (1 GB)',
	'a01041' => 'Pi 2 Model B Revision 1.1 (1 GB)',
	'a02042' => 'Pi 2 Model B (with BCM2837) Revision 1.2 (1 GB)',
	'a21041' => 'Pi 2 Model B Revision 1.1 (1 GB)',
	'a22042' => 'Pi 2 Model B (with BCM2837) Revision 1.2 (1 GB)',
	'a020a0' => 'Compute Module 3 Revision 1.0 (1 GB)',
	'a220a0' => 'Compute Module 3 Revision 1.0 (1 GB)',
	'a02100' => 'Compute Module 3+',
	'900021' => 'Model A+ Revision 1.1 (512 MB)',
	'900032' => 'Model B+ Revision 1.2 (512 MB)',
	'900062' => 'Compute Module Revision 1.1 (512 MB)',
	'900092' => 'PiZero 1.2 (512 MB)',
	'900093' => 'PiZero 1.3 (512 MB)',
	'9000c1' => 'PiZero W 1.1 (512 MB)',
	'920092' => 'PiZero Revision 1.2 (512 MB)',
	'920093' => 'PiZero Revision 1.3 (512 MB)',
	'9020e0' => 'Pi 3 Model A+ Revision 1.0 (512 MB)',
	'a02082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a22082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a32082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a52082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a22083' => 'Pi 3 Model B Revision 1.3 (1 GB)',
	'a020d3' => 'Pi 3 Model B+ Revision 1.3 (1 GB)',
	'a03111' => 'Model 4B Revision 1.1 (1 GB)',
	'b03111' => 'Model 4B Revision 1.1 (2 GB)',
	'c03111' => 'Model 4B Revision 1.1 (4 GB)',
	'b03112' => 'Model 4B Revision 1.2 (2 GB)',
	'c03112' => 'Model 4B Revision 1.2 (4 GB)',
	'b03114' => 'Model 4B Revision 1.4 (2 GB)',
	'c03114' => 'Model 4B Revision 1.4 (4 GB)',
	'd03114' => 'Model 4B Revision 1.4 (8 GB)',
	'c03130' => 'Pi 400 Revision 1.0 (4 GB)'
	);

	$cpuinfo_array = '';
	exec('grep "^Revision" /proc/cpuinfo', $cpuinfo_array);
	// We need to split this into two pieces to avoid a PHP Notice message
	$x = explode(':', array_pop($cpuinfo_array));
	$rev = trim(array_pop($x));
	if (array_key_exists($rev, $revisions)) {
		return $revisions[$rev];
	} else {
		exec('cat /proc/device-tree/model', $model);
		if (isset($model[0])) {
			return $model[0];
		} else {
			return 'Unknown Pi, rev=' . $rev;
		}
	}
}

function formatSize($bytes)
{
	$types = array('B', 'KB', 'MB', 'GB', 'TB');
	for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++) ;
	return (round($bytes, 2) . " " . $types[$i]);
}

/* Check user data for expiration.  Return true if expired, else false */
function dataExpired($dateTime, $seconds)
{
	if ($seconds === 0) return(false);

	// TODO: get working...
	return(false);
}

/* Display user data in "file". */
$num_buttons = 0;
function displayUserData($file, $displayType)
{
	global $num_buttons;
	global $status;

	if (! file_exists($file)) {
		echo "<p style='color: red'>WARNING: User data file '$file' does not exist.</p>";
		return(false);
	}
	// Format: Date/time timeout_s label type min current max warning danger
	$handle = fopen($file, "r");
	for ($i=1; ; $i++) {		// for each line in $file
		$line = fgets($handle);
		if (! $line)
			break;
		$line = trim($line);
		// Skip blank and comment lines
		if ($line === "" || substr($line, 0, 1) === "#") continue;

		$data = explode('	', $line);		// tab-separated
		$num = count($data);
		if ($num === 0) {
			return(false);
		}
		$type = $data[0];
		if ($type !== "data" && $type !== "progress" && $type !== "button") {
			echo "<p style='color: red'>WARNING: Line $i in user data file '$file' is invalid:";
			echo "<br>$line";
			echo "<br>The first field should be 'data', 'progress', or 'button'.</p>";
		} else if ($type === "data" && $displayType === $type) {
		   	if ($num != 5) {
				echo "<p style='color: red'>WARNING: Line $i in user data file '$file' is invalid:";
				echo "<br>$line";
				echo "<br>'data' lines should have 5 fields total but there were $num fields.</p>";
			} else {
				list($type, $date, $timeout_s, $label, $data) = $data;
				if (! dataExpired($date, $timeout_s)) {
					echo "<tr class='x'><td class='info-item'>$label</td><td>$data</td></tr>\n";
				}
			}
		} else if ($type === "progress" && $displayType === $type) {
		   	if ($num != 10) {
				echo "<p style='color: red'>WARNING: Line $i in user data file '$file' is invalid:";
				echo "<br>$line";
				echo "<br>'progress' lines should have 10 fields total but there were $num fields.</p>";
			} else {
				list($type, $date, $timeout_s, $label, $data, $min, $current, $max, $danger, $warning) = $data;
				if (! dataExpired($date, $timeout_s)) {
					if ($current >= $danger) {
						$status = "danger";
					} elseif ($current >= $warning) {
						$status = "warning";
					} else {
						$status = "success";
					}
					echo "<tr><td colspan='2' style='height: 10px'></td></tr>\n";
					echo "<tr><td class='info-item'>$label</td>\n";
					echo "    <td style='width: $current%' class='progress'><div class='progress-bar progress-bar-$status'\n";
					echo "    role='progressbar\n";
	   				echo "    aria-valuenow='$current' aria-valuemin='$min' aria-valuemax='$max'\n";
					echo "    style='width: $current%;'>$data\n";
					echo "    </div></td></tr>\n";
				}
			}
		} else if ($type === "button" && substr($displayType, 0, 7) === "button-") {
		   	if ($num != 8) {
				echo "<p style='color: red'>WARNING: Line $i in user data file '$file' is invalid:";
				echo "<br>$line";
				echo "<br>'button' lines should have 8 fields total but there were $num fields.</p>";
			} else {
				list($type, $date, $timeout_s, $message, $action, $btn_class, $fa_class, $btn_label) = $data;
			   	if (! dataExpired($date, $timeout_s)) {
					// We output two types of button data: the action block and the button block.
					$num_buttons++;
					if ($displayType === "button-action") {
						$u = "user_$num_buttons";
						if (isset($_POST[$u])) {
								$status->addMessage($message, "message", true);
							$result = shell_exec("$action");
							if ($result !== "") $status->addMessage($result, "message", true);
						}
					} else {	// "button-button"
						if ($num_buttons === 1) echo "<br>\n";
						echo "<button type='submit' class='btn $btn_class' style='margin-bottom:5px' name='user_$num_buttons'/><i class='fa $fa_class'></i> $btn_label</button>\n";
					}
				}
			}
		}
	}
	fclose($handle);
	$num_buttons = 0;
	return(true);
}

/**
 *
 *
 */
$status = null;
function DisplaySystem()
{
	global $status;
	$status = new StatusMessages();

	$top_dir = "/var/www";
	$camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
	$camera_settings_array = json_decode($camera_settings_str, true);
	if (isset($camera_settings_array['temptype'])) {
		$temp_type = $camera_settings_array['temptype'];
		if ($temp_type == "") $temp_type = "C";
	} else {
		$temp_type = "C";
	}

	// hostname
	exec("hostname -f", $hostarray);
	$hostname = $hostarray[0];

	// uptime
	$uparray = explode(" ", exec("cat /proc/uptime"));
	$seconds = round($uparray[0], 0);
	$minutes = $seconds / 60;
	$hours = $minutes / 60;
	$days = floor($hours / 24);
	$hours = floor($hours - ($days * 24));
	$minutes = floor($minutes - ($days * 24 * 60) - ($hours * 60));
	$uptime = '';
	if ($days != 0) {
		$uptime .= $days . ' day' . (($days > 1) ? 's ' : ' ');
	}
	if ($hours != 0) {
		$uptime .= $hours . ' hour' . (($hours > 1) ? 's ' : ' ');
	}
	if ($minutes != 0) {
		$uptime .= $minutes . ' minute' . (($minutes > 1) ? 's ' : ' ');
	}

	// mem used
	exec("free -m | awk '/Mem:/ { total=$2 } /buffers\/cache/ { used=$3 } END { print used/total*100}'", $memarray);
	$memused = floor($memarray[0]);
	// check for memused being unreasonably low, if so repeat expecting modern output of "free" command
	if ($memused < 0.1) {
		unset($memarray);
		exec("free -m | awk '/Mem:/ { total=$2 } /Mem:/ { used=$3 } END { print used/total*100}'", $memarray);
		$memused = floor($memarray[0]);
	}
	if ($memused > 90) {
		$memused_status = "danger";
	} elseif ($memused > 75) {
		$memused_status = "warning";
	} elseif ($memused > 0) {
		$memused_status = "success";
	}


	// Disk usage
	// File Usage
	/* get disk space free (in bytes) */
	$df = disk_free_space($top_dir);
	/* and get disk space total (in bytes)  */
	$dt = disk_total_space($top_dir);
	/* now we calculate the disk space used (in bytes) */
	$du = $dt - $df;
	/* percentage of disk used - this will be used to also set the width % of the progress bar */
	$dp = sprintf('%.1f', ($du / $dt) * 100);

	/* and we format the size from bytes to MB, GB, etc. */
	$df = formatSize($df);
	$du = formatSize($du);
	$dt = formatSize($dt);

	// Throttle / undervoltage status
	$x = exec("sudo vcgencmd get_throttled 2>&1");	// Output: throttled=0x12345...
	if (preg_match("/^throttled=/", $x) == false) {
			$throttle_status = "danger";
			$throttle = "Not able to get throttle status:<br>$x";
			$throttle .= "<br><span style='font-size: 150%'>Run 'sudo ~/allsky/gui/install.sh --update' to try and resolve.</style>";
	} else {
		$x = explode("x", $x);	// Output: throttled=0x12345...
//FOR TESTING: $x[1] = "50001";
		if ($x[1] == "0") {
				$throttle_status = "success";
				$throttle = "No throttling";
		} else {
			$bits = base_convert($x[1], 16, 2);	// convert hex to bits
			// See https://www.raspberrypi.com/documentation/computers/os.html#vcgencmd
			$messages = array(
				0 => 'Currently under-voltage',
				1 => 'ARM frequency currently capped',
				2 => 'Currently throttled',
				3 => 'Soft temperature limit currently active',

				16 => 'Under-voltage has occurred since last reboot.',
				17 => 'Throttling has occurred since last reboot.',
				18 => 'ARM frequency capped has occurred since last reboot.',
				19 => 'Soft temperature limit has occurred'
			);
			$l = strlen($bits);
			$throttle_status = "warning";
			$throttle = "";
			// bit 0 is the rightmost bit
			for ($pos=0; $pos<$l; $pos++) {
				$i = $l - $pos - 1;
				$bit = $bits[$i];
				if ($bit == 0) continue;
				if (array_key_exists($pos, $messages)) {
					if ($throttle == "") {
						$throttle = $messages[$pos];
					} else {
						$throttle .= "<br>" . $messages[$pos];
					}
					// current issues are a danger; prior issues are a warning
					if ($pos <= 3) $throttle_status = "danger";
				}
			}
		}
	}

	// cpu load
	$secs = 2; $q = '"';
	$cpuload = exec("(grep -m 1 'cpu ' /proc/stat; sleep $secs; grep -m 1 'cpu ' /proc/stat) | awk '{u=$2+$4; t=$2+$4+$5; if (NR==1){u1=u; t1=t;} else printf($q%.0f$q, (($2+$4-u1) * 100 / (t-t1))); }'");
	if ($cpuload < 0 || $cpuload > 100) echo "<p style='color: red; font-size: 125%;'>Invalid cpuload value: $cpuload</p>";
	if ($cpuload > 90 || $cpuload < 0) {
		$cpuload_status = "danger";
	} elseif ($cpuload > 75) {
		$cpuload_status = "warning";
	} else {
		$cpuload_status = "success";
	}

	// temperature
	$temperature = round(exec("awk '{print $1/1000}' /sys/class/thermal/thermal_zone0/temp"), 2);
	if ($temperature > 70 || $temperature < 0) {
		$temperature_status = "danger";
	} elseif ($temperature > 60 || $temperature < 10) {
		$temperature_status = "warning";
	} else {
		$temperature_status = "success";
	}
	$display_temperature = "";
	if ($temp_type == "C" || $temp_type == "B")
		$display_temperature = number_format($temperature, 1, '.', '') . "&deg;C";
	if ($temp_type == "F" || $temp_type == "B")
		$display_temperature = $display_temperature . "&nbsp; &nbsp;" . number_format((($temperature * 1.8) + 32), 1, '.', '') . "&deg;F";

	// disk usage
	if ($dp >= 90) {
		$disk_usage_status = "danger";
	} elseif ($dp >= 70 && $dp < 90) {
		$disk_usage_status = "warning";
	} else {
		$disk_usage_status = "success";
	}

	// Optional user-specified data.
	$udf = get_variable(ALLSKY_CONFIG .'/config.sh', 'WEBUI_DATA_FILES=', '');
	if ($udf !== "") {
		$user_data_files = explode(':', $udf);
		$user_data_files_count = count($user_data_files);
	} else {
		$user_data_files = "";
		$user_data_files_count = 0;
	}
	?>
	<div class="row">
		<div class="col-lg-12">
			<div class="panel panel-primary">
				<div class="panel-heading"><i class="fa fa-cube fa-fw"></i> System</div>
				<div class="panel-body">

					<?php
					if (isset($_POST['system_reboot'])) {
						echo '<div class="alert alert-warning">System Rebooting Now!</div>';
						$result = shell_exec("sudo /sbin/reboot");
					}
					if (isset($_POST['system_shutdown'])) {
						echo '<div class="alert alert-warning">System Shutting Down Now!</div>';
						$result = shell_exec("sudo /sbin/shutdown -h now");
					}
					if (isset($_POST['service_start'])) {
						echo '<div class="alert alert-warning">allsky service started</div>';
						$result = shell_exec("sudo /bin/systemctl start allsky");
					}
					if (isset($_POST['service_stop'])) {
						echo '<div class="alert alert-warning">allsky service stopped</div>';
						$result = shell_exec("sudo /bin/systemctl stop allsky");
					}
					// Optional user-specified data.
					for ($i=0; $i < $user_data_files_count; $i++) {
						displayUserData($user_data_files[$i], "button-action");
					}
					?>
					<p><?php $status->showMessages(); ?></p>

					<div class="row">
						<div class="col-md-6">
							<div class="panel panel-default">
								<div class="panel-body">
									<h4>System Information</h4>
									<table>
									<tr class="x"><td class="info-item">Hostname</td><td><?php echo $hostname ?></td></tr>
									<tr class="x"><td class="info-item">Pi Revision</td><td><?php echo RPiVersion() ?></td></tr>
									<tr class="x"><td class="info-item">Uptime</td><td><?php echo $uptime ?></td></tr>
									<tr class="x"><td class="info-item">SD Card</td><td><?php echo "$dt ($df free)" ?></td></tr>
									<?php // Optional user-specified data.
										for ($i=0; $i < $user_data_files_count; $i++) {
											displayUserData($user_data_files[$i], "data");
										}
									?>
									<tr><td colspan="2" style="height: 10px"></td></tr>
									<tr><td class="info-item">Throttle Status</td>
										<!-- Treat it like a full-width progress bar -->
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $throttle_status ?>"
										role="progressbar"
										aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
										style="width: 100%;"><?php echo $throttle ?> 
										</div></td></tr>

									<tr><td colspan="2" style="height: 10px"></td></tr>
									<tr><td class="info-item">Memory Used</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $memused_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $memused ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $memused ?>%;"><?php echo $memused ?>%
										</div></td></tr>

									<tr><td colspan="2" style="height: 10px"></td></tr>
									<tr><td class="info-item">CPU Load</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $cpuload_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $cpuload ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $cpuload ?>%;"><?php echo $cpuload ?>%
										</div></td></tr>

									<tr><td colspan="2" style="height: 10px"></td></tr>
									<tr><td class="info-item">CPU Temperature</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $temperature_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $temperature ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $temperature ?>%;"><?php echo $display_temperature ?>
										</div></td></tr>
									<tr><td colspan="2" style="height: 10px"></td></tr>
									<tr><td class="info-item">Disk Usage</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $disk_usage_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $dp ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $dp ?>%;"><?php echo $dp ?>%
										</div></td></tr>
									<?php
										// Optional user-specified data.
										for ($i=0; $i < $user_data_files_count; $i++) {
											displayUserData($user_data_files[$i], "progress");
										}
									?>
									</table>
								</div><!-- /.panel-body -->
							</div><!-- /.panel-default -->
						</div><!-- /.col-md-6 -->
					</div><!-- /.row -->

					<form action="?page=system_info" method="POST">
					<div style="margin-bottom: 20px">
						<button type="button" class="btn btn-outline btn-primary" onclick="document.location.reload(true)"><i class="fa fa-sync-alt"></i> Refresh</button>
					</div>
					<div style="margin-bottom: 15px">
						<button type="submit" class="btn btn-success" style="margin-bottom:5px" name="service_start"/><i class="fa fa-play"></i> Start allsky</button>
						<button type="submit" class="btn btn-danger" style="margin-bottom:5px" name="service_stop"/><i class="fa fa-stop"></i> Stop allsky</button>
					</div>
					<button type="submit" class="btn btn-warning" style="margin-bottom:5px" name="system_reboot"/><i class="fa fa-power-off"></i> Reboot Raspberry Pi</button>
					<button type="submit" class="btn btn-warning" style="margin-bottom:5px" name="system_shutdown"/><i class="fa fa-plug"></i> Shutdown Raspberry Pi</button>
					<?php // Optional user-specified data.
						for ($i=0; $i < $user_data_files_count; $i++) {
							displayUserData($user_data_files[$i], "button-button");
						}
					?>
					</form>

				</div><!-- /.panel-body -->
			</div><!-- /.panel-primary -->
		</div><!-- /.col-lg-12 -->
	</div><!-- /.row -->
	<?php
}
?>
