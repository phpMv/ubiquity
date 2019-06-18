<?php
namespace Ubiquity\orm\parser;

use Ubiquity\utils\base\UArray;
use Ubiquity\contents\transformation\TransformersManager;
use Ubiquity\exceptions\TransformerException;

/**
 * Parse model annotation for cache generation.
 * Ubiquity\orm\parser$ModelParser
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 *         
 */
class ModelParser {

	protected $global;

	protected $primaryKeys;

	protected $manytoOneMembers;

	protected $oneToManyMembers;

	protected $manyToManyMembers;

	protected $joinColumnMembers;

	protected $joinTableMembers;

	protected $nullableMembers = [];

	protected $notSerializableMembers = [];

	protected $fieldNames;

	protected $fieldTypes = [];

	protected $transformers = [];

	protected $accessors = [];

	protected $yuml;

	public function parse($modelClass) {
		$instance = new $modelClass();
		$this->primaryKeys = Reflexion::getKeyFields($instance);
		$this->oneToManyMembers = Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@oneToMany");
		$this->manytoOneMembers = Reflexion::getMembersNameWithAnnotation($modelClass, "@manyToOne");
		$this->manyToManyMembers = Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@manyToMany");
		$this->joinColumnMembers = Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@joinColumn");
		$this->joinTableMembers = Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@joinTable");
		$this->transformers = Reflexion::getMembersAnnotationWithAnnotation($modelClass, "@transformer");
		$yuml = Reflexion::getAnnotationClass($modelClass, "@yuml");
		if (\sizeof($yuml) > 0)
			$this->yuml = $yuml[0];
		$properties = Reflexion::getProperties($instance);
		foreach ($properties as $property) {
			$propName = $property->getName();
			$fieldName = Reflexion::getFieldName($modelClass, $propName);
			$this->fieldNames[$propName] = $fieldName;
			$nullable = Reflexion::isNullable($modelClass, $propName);
			$serializable = Reflexion::isSerializable($modelClass, $propName);
			if ($nullable)
				$this->nullableMembers[] = $propName;
			if (! $serializable)
				$this->notSerializableMembers[] = $propName;
			$type = Reflexion::getDbType($modelClass, $propName);
			if ($type === false)
				$type = "mixed";
			$this->fieldTypes[$propName] = $type;
			$accesseur = "set" . ucfirst($propName);
			if (! isset($this->accessors[$fieldName]) && method_exists($modelClass, $accesseur)) {
				$this->accessors[$fieldName] = $accesseur;
			}
		}

		$this->global["#tableName"] = Reflexion::getTableName($modelClass);
	}

	public function asArray() {
		$result = $this->global;
		$result["#primaryKeys"] = $this->primaryKeys;
		$result["#manyToOne"] = $this->manytoOneMembers;
		$result["#fieldNames"] = $this->fieldNames;
		$result["#fieldTypes"] = $this->fieldTypes;
		$result["#nullable"] = $this->nullableMembers;
		$result["#notSerializable"] = $this->notSerializableMembers;
		$result["#transformers"] = [];
		$result["#accessors"] = $this->accessors;
		if (isset($this->yuml))
			$result["#yuml"] = $this->yuml->getPropertiesAndValues();
		foreach ($this->oneToManyMembers as $member => $annotation) {
			$result["#oneToMany"][$member] = $annotation->getPropertiesAndValues();
		}
		foreach ($this->manyToManyMembers as $member => $annotation) {
			$result["#manyToMany"][$member] = $annotation->getPropertiesAndValues();
		}

		foreach ($this->joinTableMembers as $member => $annotation) {
			$result["#joinTable"][$member] = $annotation->getPropertiesAndValues();
		}

		if (class_exists("Ubiquity\\contents\\transformation\\TransformersManager")) {
			TransformersManager::start();
			foreach ($this->transformers as $member => $annotation) {
				$goodTransformer = false;
				if (array_search($member, $this->notSerializableMembers, false) !== false) {
					throw new TransformerException(sprintf('%s member is not serializable and does not supports transformers!', $member));
				}
				$trans = TransformersManager::getTransformerClass($annotation->name);
				if ($trans == null) {
					throw new TransformerException(sprintf('%s value is not a declared transformer.', $annotation->name));
				} else {
					foreach (TransformersManager::TRANSFORMER_TYPES as $transType => $transInterface) {
						if (is_subclass_of($trans, $transInterface, true)) {
							$goodTransformer = true;
							$result["#transformers"][$transType][$member] = $trans;
						}
					}
					if (! $goodTransformer) {
						throw new TransformerException(sprintf('%s does not implements %s', $trans, 'TransformerInterfaces'));
					}
				}
			}
		}

		foreach ($this->joinColumnMembers as $member => $annotation) {
			$result["#joinColumn"][$member] = $annotation->getPropertiesAndValues();
			$result["#invertedJoinColumn"][$annotation->name] = [
				"member" => $member,
				"className" => $annotation->className
			];
		}
		return $result;
	}

	public function __toString() {
		return "return " . UArray::asPhpArray($this->asArray(), "array") . ";";
	}
}
