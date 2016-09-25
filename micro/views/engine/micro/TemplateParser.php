<?php
namespace micro\views\engine\micro;
/**
 * Moteur de template pour les fichiers d'extension phtml
 * @author jc
 * @package views
 *
 */
class TemplateParser{
	private $fileContent;
	public function __construct($fileName){
		$this->fileContent=file_get_contents($fileName);
	}
	private function parse($html){
		$startPoint = '{{';
		$endPoint = '}}';
		$result = preg_replace('/('.preg_quote($startPoint).')(.*?)('.preg_quote($endPoint).')/sim', '<?php echo $2 ?>', $html);
		return $result;
	}
	public function __toString(){
		return $this->parse($this->fileContent);
	}
}