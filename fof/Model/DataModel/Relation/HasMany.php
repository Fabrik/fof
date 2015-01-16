<?php
/**
 * @package     FOF
 * @copyright   2010-2015 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Model\DataModel\Relation;

use FOF30\Model\DataModel;
use FOF30\Model\DataModel\Relation;

defined('_JEXEC') or die;

/**
 * HasMany (1-to-many) relation: this model is a parent which has zero or more children in the foreign table
 *
 * For example, parentModel is Users and foreignModel is Articles. Each user has zero or more articles.
 */
class HasMany extends Relation
{
	/**
	 * Public constructor. Initialises the relation.
	 *
	 * @param   DataModel $parentModel       The data model we are attached to
	 * @param   string    $foreignModelClass The class name of the foreign key's model
	 * @param   string    $localKey          The local table key for this relation, default: parentModel's ID field name
	 * @param   string    $foreignKey        The foreign key for this relation, default: parentModel's ID field name
	 * @param   string    $pivotTable        IGNORED
	 * @param   string    $pivotLocalKey     IGNORED
	 * @param   string    $pivotForeignKey   IGNORED
	 */
	public function __construct(DataModel $parentModel, $foreignModelClass, $localKey = null, $foreignKey = null, $pivotTable = null, $pivotLocalKey = null, $pivotForeignKey = null)
	{
		parent::__construct($parentModel, $foreignModelClass, $localKey, $foreignKey, $pivotTable, $pivotLocalKey, $pivotForeignKey);

		if (empty($this->localKey))
		{
			$this->localKey = $parentModel->getIdFieldName();
		}

		if (empty($this->foreignKey))
		{
			$this->foreignKey = $this->localKey;
		}
	}

	/**
	 * Applies the relation filters to the foreign model when getData is called
	 *
	 * @param DataModel  $foreignModel   The foreign model you're operating on
	 * @param DataModel\Collection $dataCollection If it's an eager loaded relation, the collection of loaded parent records
	 *
	 * @return boolean Return false to force an empty data collection
	 */
	protected function filterForeignModel(DataModel $foreignModel, DataModel\Collection $dataCollection = null)
	{
		// Decide how to proceed, based on eager or lazy loading
		if (is_object($dataCollection))
		{
			// Eager loaded relation
			if (!empty($dataCollection))
			{
				// Get a list of local keys from the collection
				$values = array();

				/** @var $item DataModel */
				foreach ($dataCollection as $item)
				{
					$v = $item->getFieldValue($this->localKey, null);

					if (!is_null($v))
					{
						$values[] = $v;
					}
				}

				// Keep only unique values
				$values = array_unique($values);

				// Apply the filter
				if (!empty($values))
				{
					$foreignModel->where($this->foreignKey, 'in', $values);
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			// Lazy loaded relation; get the single local key
			$localKey = $this->parentModel->getFieldValue($this->localKey, null);

			if (is_null($localKey))
			{
				return false;
			}

			$foreignModel->where($this->foreignKey, '==', $localKey);
		}

		return true;
	}

	/**
	 * Returns the count subquery for DataModel's has() and whereHas() methods.
	 *
	 * @return \JDatabaseQuery
	 */
	public function getCountSubquery()
	{
		// Get a model instance
		$foreignModel = $this->getForeignModel();
		$foreignModel->setIgnoreRequest(true);

		$db = $foreignModel->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn($foreignModel->getTableName()) . ' AS ' . $db->qn('reltbl'))
			->where($db->qn('reltbl') . '.' . $db->qn($foreignModel->getFieldAlias($this->foreignKey)) . ' = '
				. $db->qn($this->parentModel->getTableName()) . '.'
				. $db->qn($this->parentModel->getFieldAlias($this->localKey)));

		return $query;
	}

	/**
	 * Returns a new item of the foreignModel type, pre-initialised to fulfil this relation
	 *
	 * @return DataModel
	 *
	 * @throws DataModel\Relation\Exception\NewNotSupported when it's not supported
	 */
	public function getNew()
	{
		// Get a model instance
		$foreignModel = $this->getForeignModel();
		$foreignModel->setIgnoreRequest(true);

		// Prime the model
		$foreignModel->setFieldValue($this->foreignKey, $this->parentModel->getFieldValue($this->localKey));

		// Make sure we do have a data list
		if (!($this->data instanceof DataModel\Collection))
		{
			$this->getData();
		}

		// Add the model to the data list
		$this->data->add($foreignModel);

		return $this->data->last();
	}
} 