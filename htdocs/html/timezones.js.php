
var tzList = {};
var cur_region;
var cur_zone;

$(window).load(function() {
	var regions = $('#u_tz_region');
	var zones   = $('#u_tz_zone');
	
	for(var region in tzList) {
	
		$('<option>')
			.val(region)
			.text(region)
			.appendTo(regions);
	
	}
	
	regions.change(function() {
		zones.empty();
		for(var zoneIndex in tzList[this.value]) {
			var zoneName = tzList[this.value][zoneIndex]
			$('<option>')
				.val(zoneName)
				.text(zoneName)
				.appendTo(zones);
			
		}
	});
	
	if (cur_region) {
		regions.val(cur_region);
		regions.change();
		
		if (cur_zone) {
			zones.val(cur_zone);
			zones.change();
		}
		
	}
});

<?php
$err = error_reporting(0);

$tzones = array();

foreach( DateTimeZone::listIdentifiers() as $zone) {
	$info = explode("/", $zone, 2);//	Convert 'Region/Zone' -> ['Region', 'Zone']
	if (isset($info[1])) {// Not for UTC or GMT
		$tzones[$info[0]][] = $info[1];
	}
}

echo "tzList = ".json_encode($tzones);

if ($_GET['current']) {
	list($region, $zone) = explode("/", $_GET['current'], 2);
	echo ";\ncur_region = ".json_encode($region);
	echo ";\ncur_zone = ".json_encode($zone);
}
error_reporting($err);
