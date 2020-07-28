<?php


ini_set("memory_limit","512M");
set_time_limit(360);


require_once('generateStandings.php');

function getAllContestants(){

	$aiub = "American International University Bangladesh";
	$url = "https://codeforces.com/api/user.ratedList";
	$list = file_get_contents($url);
	$list = json_decode($list);
	$list = $list->result;

	$userList = array();

	foreach ($list as $key => $user) {
		if( isset($user->organization) ){
			if( $user->organization == $aiub ){
				array_push($userList,trim($user->handle));
			}
		}
	}

	return $userList;

}



function getWhiteList(){
	$whiteList = array();
	$file = fopen("WHITE.txt", "r") or die("Unable to open file!");
	while(!feof($file)) {
	  array_push($whiteList, trim(fgets($file)));
	}
	fclose($file);
	return $whiteList;
}

function getBlackList(){
	$blackList = array();
	$file = fopen("BLACK.txt", "r") or die("Unable to open file!");
	while(!feof($file)) {
	  array_push($blackList, trim(fgets($file)));
	}
	fclose($file);
	return $blackList;
}


function getContestList(){
	$arr = array();
	$file = fopen("CONTESTS.txt", "r") or die("Unable to open file!");
	while(!feof($file)) {
	  array_push($arr, (int)fgets($file));
	}
	fclose($file);
	return $arr;
}


function addToBlackList($handle){
	$file = fopen("BLACK.txt","a") or die("Unable to open file!");
	fwrite($file,"\n".$handle);
	fclose($file);
}



function tournamentStanding(){

	$blackList = getBlackList();
	$whiteList = getWhiteList();
	$contestList = getContestList();
	$allParticipants = getAllContestants();
	$tournamentParticipants = generateTournamentStanding($allParticipants,$contestList);
	$whiteParticipants = array();
	foreach ($tournamentParticipants as $key => $participant) {
		$handle = $participant->handle;
		if( in_array($handle,$blackList) )continue;
		if( in_array($handle,$whiteList) ){
			array_push($whiteParticipants,$handle);
		}
		else{
			addToBlackList($handle);
		}
	}
	$N = count($contestList);
	$finalScoreTable = generateTournamentStanding($whiteParticipants,$contestList);
	return $finalScoreTable;
}




$table = tournamentStanding();
$cnt = 0;
echo "<table>";

echo "<b><tr>";
echo "<td>Rank</td>";
echo "<td>Participant Handle</td>";
echo "<td>Score</td>";
$n = $table[0]->numberOfContest;
for($i=0;$i<$n;$i++){
	echo "<td> Contest ".($cnt - $i - 1)."</td>";
}
echo "</tr><b>";



foreach ($table as $key => $row) {
	$cnt++;
	$row->show($cnt);
}



echo "</table>";

?>