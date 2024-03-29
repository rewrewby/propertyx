<?php

/**
 * Subclass for performing query and update operations on the 'sf_asset_folder' table.
 *
 * 
 *
 * @package plugins.sfAssetsLibraryPlugin.lib.model
 */ 
class sfAssetFolderPeer extends BasesfAssetFolderNestedSetPeer
{

  /**
   * Recursively creates parent folders 
   *
   * @param string $path
   * @return sfAssetFolder
   */
  public static function createFromPath($path)
  {
    $path = self::cleanPath($path);
    list($parent_path, $name) = sfAssetsLibraryTools::splitPath($path);
    if (!$parent_folder = self::retrieveByPath($parent_path))
    {
      $parent_folder = self::createFromPath($parent_path);
      $parent_folder->save();
    }
    $folder = new sfAssetFolder();
    $folder->setName($name);
    $folder->setRelativePath($path);
    $folder->insertAsLastChildOf($parent_folder);
    $folder->save();
    
    return $folder;
  }
	
  /**
   * Enter description here...
   *
   * @param string $name
   * @param integer $limit
   * @return array of folders
   */
	static public function retrieveByPathForSelect($path, $limit)
	{
		$criteria = new Criteria();
		$criteria->add(sfAssetFolderPeer::RELATIVE_PATH, '%'.$path.'%', Criteria::LIKE);
		$criteria->addAscendingOrderByColumn(sfAssetFolderPeer::NAME);
		$criteria->setLimit($limit);
		
		$folders = array();
    foreach (self::doSelect($criteria) as $folder)
    {
      $folders[$folder->getId()] = (string) $folder->getRelativePath();
    }
 
    return $folders;
	}
  
  /**
   * Retrieves folder by relative path
   *
   * @param string $path
   * @param string $separator
   * @return sfAssetFolder
   */
  public static function retrieveByPath($path = '', $separator = DIRECTORY_SEPARATOR)
  {
    $path = self::cleanPath($path);
    $c = new Criteria();
    $c->add(sfAssetFolderPeer::RELATIVE_PATH, $path ? $path : null);
    
    return sfAssetFolderPeer::doSelectOne($c);
  }
  
  /**
   * Gives an options array with all folders
   *
   * @param bool $includeRoot
   * @return array options array
   */
  public static function getAllPaths($includeRoot = true)
  {
    $root = self::retrieveRoot();
    $allDirs = $root->getDescendants();
    if($includeRoot)
    {
      $allDirs[] = $root;
    }
    $allDirs = self::sortByName($allDirs);
    $options = array();
    foreach ($allDirs as $folder)
    {
      $options[$folder->getRelativePath()] = $folder->getRelativePath();
    }
    
    return $options;
  }
  
  public static function getAllNonDescendantsPaths($folder)
  {
    $c = new Criteria();
    $criterion1 = $c->getNewCriterion(self::TREE_LEFT, $folder->getLeftValue(), Criteria::LESS_THAN);
    $criterion2 = $c->getNewCriterion(self::TREE_RIGHT, $folder->getRightValue(), Criteria::GREATER_THAN);
    $criterion1->addOr($criterion2);
    $c->add($criterion1);
    
    return self::getAllPaths(true, $c);
  }
  
  public static function sortByName($dirs = array())
  {
    $sortedDirs = array();
    foreach($dirs as $dir)
    {
      $key = strtolower($dir->getRelativePath());
      if(array_key_exists($key, $sortedDirs))
      {
        $key .= time();
      }
      $sortedDirs[$key] = $dir;
    }
    ksort($sortedDirs);
    
    return $sortedDirs;
  }

  public static function sortByDate($dirs = array())
  {
    $sortedDirs = array();
    foreach($dirs as $dir)
    {
      $sortedDirs[$dir->getCreatedAt('U')] = $dir;
    }
    krsort($sortedDirs);
    
    return $sortedDirs;
  }

  /**
   * Sanitize path
   *
   * @param string $path
   * @return string
   */
  public static function cleanPath($path)
  {
    $path = trim($path, '/');
    $root_name = sfConfig::get('app_sfAssetsLibrary_upload_dir', 'media');
    if(!$path)
    {
      $path = $root_name;
    }
    elseif(strpos($path, $root_name) !== 0)
    {
      $path = $root_name . '/' . $path;
    }
    return $path;
  }
}
