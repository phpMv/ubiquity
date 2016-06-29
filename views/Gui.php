<?php
namespace micro\views;
/**
 * Classe technique destinée à la conception des interfaces
 * @author jc
 * @package views
 *
 */
class Gui{
	/**
	 * Affiche un objet ou un tableau d'objets en appliquant au préalable la méthode $method à chacun d'entre eux
	 * @param mixed $values Valeur(s) à afficher
	 * @param string $method Méthode de la classe GUI ou de la classe de $value
	 * @param string $implode séparateur
	 */
	public static function show($values,$method='toString',$implode="<br/>"){
		if(is_array($values)){
			foreach ($values as $v){
				Gui::showOne($v,$method);
				echo $implode;
			}
		}else
			Gui::showOne($values,$method);
	}

	/**
	 * Retourne un objet ou un tableau d'objets en appliquant au préalable la méthode $method à chacun d'entre eux
	 * @param mixed $values Valeur(s) à afficher
	 * @param string $method Méthode de la classe GUI ou de la classe de $value
	 * @param string $implode séparateur
	 */
	public static function get($values,$method='toString',$implode="<br/>"){
		$result="";
		if(is_array($values)){
			foreach ($values as $v){
				$result.=Gui::getOne($v,$method).$implode;
			}
		}else
			$result=Gui::getOne($values,$method);
		return $result;
	}

	/**
	 * Affiche un objet $value en lui ayant au préalable appliqué la méthode $method
	 * @param Object $value
	 * @param string $method Méthode de la classe GUI ou de la classe de $value
	 */
	public static function showOne($value,$method='toString'){
		echo Gui::getOne($value,$method);
	}

	/**
	 * Retourne un objet $value en lui ayant au préalable appliqué la méthode $method
	 * @param Object $value
	 * @param string $method Méthode de la classe GUI ou de la classe de $value
	 */
	public static function getOne($value,$method='toString'){
		if(method_exists("GUI", $method)){
			$value=GUI::$method($value);

		}else{
			if(method_exists($value, $method)){
				$value=$value->$method();
			}else{
				$value=$value.'';
			}
		}
		return $value;
	}

	public static function addDelete($value){
		return "<tr><td class='element'><input title='Sélectionner' type='checkbox' class='ck' id='ck".$value->getId()."' value='".$value->getId()."'><span title='Modifier...' class='update' id='update".$value->getId()."'>&nbsp;".$value->toString()."<span></td><td><span title='Supprimer...' class='delete' id='delete".$value->getId()."'>&nbsp;</span></td></tr>";
	}

	public static function toSelect($value){
		return "<option class='element' id='element".$value->getId()."' value='".$value->getId()."'>".$value->toString()."</option>";
	}

	/**
	 * Retourne un objet ou un tableau d'objets sous forme de liste HTML (select)
	 * @param mixed $values Valeur(s) à afficher
	 * @param string $value Valeur actuelle
	 */
	public static function select($values,$value,$first=null){
		$result="";
		if($first){
			$result.="<option class='element'>".$first."</option>";
		}
		foreach ($values as $v){
			$selected="";
			$id=$v;
			if(is_object($v)===true){
				$id=$v->getId();
			}
			if($id===$value){
				$selected="selected";
			}
			$result.="<option ".$selected." class='element' id='element".$id."' value='".$id."'>".$v."</option>";
		}
		return $result;
	}

	/**
	 * Retourne l'expression $singulier au pluriel en fonction du nombre $nb
	 * @param string $singulier
	 * @param string $pluriel
	 * @param int $nb
	 */
	public static function pluriel($singulier,$pluriel,$nb){
		if($nb==0){
			$result="Aucun ".$singulier;
		}else{
			$result=sprintf(ngettext("%d ".$singulier, "%d ".$pluriel, $nb),$nb);
		}
		return $result;
	}
}