<?php




$scoreToGet = array(
	100,70,56,47,41,36,32,28,25,22,20,18,16,14,12,10,8,6,4,2
);



class Participant{

	public $handle;
	public $performances;
	public $scorevalues;
	public $score;
	public $numberOfContest;


	static function cmp($a,$b){
		return $a->score < $b->score;
	}

	public function show($id){

		echo "<tr>";
		echo "<td>".$id."</td>";
		echo "<td><b>".$this->handle." <b></td><td> <b>".$this->score."  <b></td>";
		for($i=0;$i<$this->numberOfContest;$i++){
			if( isset($this->performances[$i]) ){
				echo "<td>".$this->performances[$i]."</td>";
			}
			else{
				echo "<td>0</td>";
			}
		}
		echo "</tr>";
	}

	public function calculateScore(){

		$n = count($this->scorevalues);
		if( $n == 0 ){
			$this->score = 0;
			return;
		}
		$x = ceil( (7*$this->numberOfContest)/10.0 );
		sort($this->scorevalues);
		$this->scorevalues = array_reverse($this->scorevalues,false);
		for($i=0;$i<$x;$i++){
			$this->score += $this->scorevalues[$i];
		}
	}

	public function addPerformance($cid,$rank){
		$this->performances[$cid] = 1;
		$val = 1;
		global $scoreToGet;
		if( $rank < 21 ){
			$this->performances[$cid] = $scoreToGet[$rank-1];
			$val = $scoreToGet[$rank-1];
		}
		array_push($this->scorevalues, $val);
	}

	public function __construct($name,$cnt){
		$this->handle = $name;
		$this->score = 0;
		$this->performances = array();
		$this->scorevalues = array();
		$this->numberOfContest = $cnt;
	}
}






function generateTournamentStanding( $contestantList,$contestList ){



	$scoreTable = array();
	$urlSuffix = "&handles=";
	$cnt = count($contestList);
	foreach ($contestantList as $key => $contestant) {
		$scoreTable[$contestant] = new Participant($contestant,$cnt);
		$urlSuffix .= $contestant .";";
	}



	$cid = 0;
	foreach ($contestList as $key => $contest) {
		$url = "https://codeforces.com/api/contest.standings?contestId=".$contest.$urlSuffix;
		$contents = file_get_contents($url);
		$contents = json_decode($contents,false);
		$rows = $contents->result->rows ;
		$r = 1;
		foreach ( $rows as $key => $row) {
			# code...
			$handle = $row->party->members[0]->handle;
			$rank = $r;
			$r++;
			$scoreTable[$handle]->addPerformance($cid,$rank);
		}
		$cid++;
	}


	$tableToPrint = array();
	foreach ($scoreTable as $key => $party) {
		$party->calculateScore();
		if( $party->score > 0 )array_push( $tableToPrint, $party);
	}
	usort($tableToPrint,array("Participant","cmp"));
	return $tableToPrint;
	
}



// $contestantList = array('gusion','AmdSadi','Mujahid');
// $contestList = array(1375,1371,1373,566,1369,1368);


// $tableToPrint = generateTournamentStanding($contestantList,$contestList);

// $N = count($contestList);
// foreach ($tableToPrint as $key => $participant) {
// 	$participant->numberOfContest = $N;
// 	$participant->show();
// }

?>