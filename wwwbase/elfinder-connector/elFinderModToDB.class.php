<?php

autoloadModelsClass('Visual');
autoloadModelsClass('BaseObject');
autoloadModelsClass('DatedObject');

class elFinderModToDB extends Visual {

/** Using the bind option in the connector, the elFinder stores the commands
  * that the user has made in $cmd and the paths to the files or folders
  * that have been modified in $result. Thus, the action function within this class
  * listens to what modifications were made and adds/changes entries in the
  * Visual table accordingly.
  */
public function action($cmd, $result, $args, $elfinder) {

  switch($cmd){
    case 'upload': 
    if(!empty($result['added'])) {
      foreach($result['added'] as $file) {
        $path = Visual::getPath($elfinder->realpath($file['hash']));
        Visual::$cmd = $cmd;

        $line = Model::factory('Visual')->create();
        $line->path = $path;
        $line->userId = session_getUserId();
        $line->save();
      }
    }
    break;
			
    case 'rm':
    if(!empty($result['removed'])) {
      foreach($result['removed'] as $file) {
        $path = Visual::getPath($file['realpath']);

        $line = Visual::get_by_path($path);
        /** rm command stores its info in $result['removed'] even for folders,
          * thus, it first checks if there were entries in the table that
          * matched the value in $file. */
        if(!empty($line)) {
          $line->delete();
        }
      }
    }
    break;

    case 'rename':
      if(!empty($result['removed'])) {
      /** As the rename array stores both files and folders first it
        * identifies the type. If a folder is renamed, then all the
        * paths of the files within it are modified accordingly. */
        $oldPath = Visual::getPath($result['removed'][0]['realpath']);
        $newPath = Visual::getPath($elfinder->realpath($result['added'][0]['hash']));
        $entries = Model::factory('Visual')->where_like('path', "{$oldPath}/%")->find_many();
        Visual::$cmd = $cmd;

        if(!empty($entries)) {
          /** Directory was renamed **/
          foreach($entries as $entry) {
            $entry->path = str_replace($oldPath, $newPath, $entry->path);
            $entry->save();
          }

        } else {
          /** Otherwise it changes the file path within the table */
          $line = Visual::get_by_path($oldPath);

          if(!empty($line)) {
            $line->save();
          }
        }
      }
      break;

      case 'paste':
      /** Cut - Paste */
      if(!empty($result['removed'])) {
        foreach($result['removed'] as $i => $file) {
          $oldPath = Visual::getPath($file['realpath']);
          $newPath = Visual::getPath($elfinder->realpath($result['added'][$i]['hash']));
          $line = Visual::get_by_path($oldPath);

          if(!empty($line)) {
            $line->path = $newPath;
            $line->save();
          }
        }

      /** Copy - Paste */
      } else if(!empty($result['added'])) {
        foreach($result['added'] as $file) {
          $path = Visual::getPath($elfinder->realpath($file['hash']));

          $line = Model::factory('Visual')->create();
          $line->path = $path;
          $line->save();
        }
      }
      break;
    }
  }
}
