<?php
class Console {
	public static function readline(){
		return rtrim(fgets(STDIN));
	}

	public static function question($prompt,array $propositions=null){
		echo $prompt;
		if(is_array($propositions)){
			echo " (".implode("/", $propositions).")\n";
			do{
				$answer=self::readline();
			}while(array_search($answer, $propositions)===false);
		}else
			$answer=self::readline();

		return $answer;
	}

	public static function isYes($answer){
		return array_search($answer, ["yes","y"])!==false;
	}
}
