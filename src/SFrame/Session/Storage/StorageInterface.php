<?php namespace SFrame\Session\Storage;

/**
 * Save Handler Cache
 */

interface StorageInterface
{
    public function open($save_path, $name);
    public function close();
    public function read($id);
    public function write($id, $data);
    public function destroy($id);
    public function gc($maxlifetime);
}
