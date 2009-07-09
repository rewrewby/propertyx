<?php
/**
 * This file is part of the propertyx package.
 * (c) 2009 Daniel Londero <daniel.londero@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Property model.
 *
 * @package    propertyx
 * @subpackage lib.model
 * @author     Daniel Londero <daniel.londero@gmail.com>
 */
class Property extends BaseProperty
{
	public function getMunicipalitySlug()
	{
	  return Property::slugify($this->getMunicipality());
	}
	 
	public function getTypeSlug()
	{
	  return Property::slugify($this->getType());
	}
	 
	public function getTypologySlug()
	{
	  return Property::slugify($this->getTypology());
	}
}
