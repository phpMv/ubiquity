<?php

namespace Ubiquity\views\engine\twig;

use Ubiquity\utils\base\UString;
use Ubiquity\views\engine\TemplateGenerator;

class TemplateParser {

	private TemplateGenerator $generator;

	private string $varPattern='@\{\{\s?(.*?)\s?\}\}@';

	private string $blockPattern='@\{\%\s?block\s(.*?)\s?\%\}@';

	public function __construct(TemplateGenerator $generator) {
		$this->generator=$generator;
	}

	private function parseVar(string $varStr): array {
		$parts=\explode('|',$varStr);
		return ['name'=>trim($parts[0]),'safe'=>isset($parts[1])];
	}

	private function parseForeach(string $foreachStr): array {
		$parts=\explode('in',$foreachStr);
		$kv=explode(',',$parts[0]);
		$v=\trim($parts[0]);$k=null;
		if (\count($kv)>1) {
			$k=\trim($kv[0]);
			$v=\trim($kv[1]);
		}
		return ['array'=>trim($parts[1]),'value'=>$v,'key'=>$k];
	}

	private function getExpressionPattern(string $clause){
		return '@\{\%\s?'.$clause.'\s(.*?)\s?\%\}@';
	}

	protected function parseAllVars(string $text): string {
		$result=$text;
		if(\preg_match_all($this->varPattern, $text,$matches)){
			$originals=$matches[0];
			$vars=$matches[1];
			foreach ($vars as $index=>$varStr){
				$variableInfos=$this->parseVar($varStr);
				$result=\str_replace($originals[$index], $this->generator->insertVariable($variableInfos['name'], $variableInfos['safe']),$result);
			}
		}
		return $result;
	}

	protected function parseAllConditions(string $text): string {
		$result=$text;
		if(\preg_match_all($this->getExpressionPattern('if'), $text,$matches)){
			$originals=$matches[0];
			$conditions=$matches[1];
			foreach ($conditions as $index=>$cond){
				$result=\str_replace($originals[$index], $this->generator->condition($cond),$result);
			}
		}
		return $result;
	}

	protected function parseAllForeachs(string $text): string {
		$result=$text;
		if(\preg_match_all($this->getExpressionPattern('for'), $text,$matches)){
			$originals=$matches[0];
			$foreachs=$matches[1];
			foreach ($foreachs as $index=>$foreach){
				$foreachInfos=$this->parseForeach($foreach);
				$result=\str_replace($originals[$index], $this->generator->foreach($foreachInfos['array'],$foreachInfos['value'],$foreachInfos['key']),$result);
			}
		}
		return $result;
	}

	protected function parseAllBlock(string $text): string {
		$result=$text;
		if(\preg_match_all($this->blockPattern, $text,$matches)){
			$originals=$matches[0];
			$blocks=$matches[1];
			foreach ($blocks as $index=>$block){
				$blockName=\trim($block);

				$result=\str_replace($originals[$index], $this->generator->openBlock($blockName),$result);
			}
		}
		return $result;
	}

	protected function parseCallback(string $text,string $pattern,$callback): string {
		$result=$text;
		if(\preg_match_all($pattern, $text,$matches)){
			$originals=$matches[0];
			foreach ($originals as $elm){
				$result=\str_replace($elm, $callback(),$result);
			}
		}
		return $result;
	}

	protected function parseCallbackWithVar(string $text,string $pattern,$callback): string {
		$result=$text;
		if(\preg_match_all($pattern, $text,$matches)){
			$originals=$matches[0];
			$vars=$matches[1];
			foreach ($vars as $index=>$elm){
				$elm=\trim($elm);
				$result=\str_replace($originals[$index], $callback($elm),$result);
			}
		}
		return $result;
	}

	public function parseFileContent(string $fileContent):string {
		$gen=$this->generator;
		$result=$this->parseCallback($fileContent,'@\{\{\s?nonce\s?\}\}@',function(){
			return $this->generator->getNonce();
		});
		$result=$this->parseCallback($result,"@\{\s?'nonce':\s?nonce\s?\}@",function(){
			return $this->generator->getNonceArray();
		});
		$result=$this->parseCallback($result,'@\{\{\s?_self\s?\}\}@',function(){
			return $this->generator->getSelf();
		});
		$result=$this->parseAllVars($result);
		$result=$this->parseAllBlock($result);
		$result=$this->parseCallback($result,'@\{\%\s?endblock\s?\%\}@',function(){
			return $this->generator->closeBlock();
		});
		$result=$this->parseAllForeachs($result);
		$result=$this->parseCallback($result,'@\{\%\s?endfor\s?\%\}@',function(){
			return $this->generator->endForeach();
		});
		$result=$this->parseAllConditions($result);
		$result=$this->parseCallback($result,'@\{\%\s?endif\s?\%\}@',function(){
			return $this->generator->endCondition();
		});
		$result=$this->parseCallbackWithVar($result,'@\{\%\s?extends\s?(.*?)\s?\%\}@',function($name){
			$asVariable=!UString::containsValues(['"',"'"],$name);
			return $this->generator->extendsTemplate($name,$asVariable);
		});
		$result=$this->parseCallbackWithVar($result,'@\{\%\s?include\s?(.*?)\s?\%\}@',function($name){
			$asVariable=!UString::containsValues(['"',"'"],$name);
			return $this->generator->includeFile($name,$asVariable);
		});
		$result=$this->parseCallbackWithVar($result,'@\{\{\s(.*?)\s?\}\}@',function($content){
			return $this->generator->getOpenVarTag().$content.$this->generator->getCloseVarTag();
		});
		$result=$this->parseCallbackWithVar($result,'@\{\%\s(.*?)\s?\%\}@',function($content){
			return $this->generator->getOpenExpressionTag().$content.$this->generator->getCloseExpressionTag();
		});
		return $result;
	}

}