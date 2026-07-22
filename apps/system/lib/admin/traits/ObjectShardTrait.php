<?php
/**
 * ObjectShardTrait — Shard queue management methods extracted from Object_Manager.
 *
 * Methods: enable_shard_queue, disable_shard_queue, is_shard_queue_enable, run_shard_task
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

use sharder\lib\sharder;

trait ObjectShardTrait
{
    /**
     * @var bool
     */
    private $shard_queue = false;

    function enable_shard_queue()
    {
        $this->shard_queue = true;
    }

    function disable_shard_queue()
    {
        $this->shard_queue = false;
    }

    function is_shard_queue_enable()
    {
        return $this->shard_queue;
    }

    function run_shard_task()
    {
        if (($this->getConfigValue('apps.sharder.api_key') or $this->getConfigValue('apps.sharder.s3.enable')) and $this->is_shard_queue_enable()) {
            if (!is_object($this->sharder)) {
                $this->sharder = new sharder();
            }
            $this->sharder->remove_remote_files_from_queue($this->getServerFullUrl(true));
            $this->disable_shard_queue();
        }
        return false;
    }
}
