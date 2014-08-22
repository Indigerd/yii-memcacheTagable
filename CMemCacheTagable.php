<?php
/**
 * @author      Alexander Stepanenko <alex.stepanenko@gmail.com>
 * @license     http://mit-license.org/
 */


class CMemCacheTagable extends CMemCache
{

    public $tagKeyPrefix = 'tag_';

    public function set($id, $value, $expire = 0, $tags = null)
    {

    }

    public function setMany(array $data, $expire = 0, $tags = null)
    {

    }

    public function getByTag($tags, $byIntersect = false)
    {
        $result  = [];
        $tags    = is_array($tags) ? array_map([$this, 'generateTagKey'], array_values($tags)) : $this->generateTagKey($tags);
        $tagData = $this->getTagData($tags);
        if ($tagData !== null) {
            $result = $this->getValues($tagData);
        }
        return $result;
    }

    public function deleteByTag($tags)
    {
        $tags    = is_array($tags) ? array_map([$this, 'generateTagKey'], array_values($tags)) : $this->generateTagKey($tags);
        $tagData = $this->getTagData($tags);
        if ($tagData !== null) {
            $this->deleteMany($tagData);
            if (is_array($tags)) {
                foreach ($tags as $tag) $this->deleteValue($tag);
            } else {
                $this->deleteValue($tags);
            }
        }
    }

    protected function deleteMany(array $ids) {
        $ids = array_map([$this, 'generateUniqueKey'], $ids);
        foreach ($ids as $id) $this->deleteValue($id);
    }

    protected function getTagData($tags)
    {
        if (is_array($tags)) {
            $tagsData = $this->getValues($tags);
            if (!empty($tagsData)) {
                $tagData = array_unique(call_user_func_array('array_merge', $tagsData));
            }
        } else {
            $tagData = $this->getValue($tags);
        }
        return $tagData;
    }

    protected function generateTagKey($tag)
    {
        return $this->hashKey ? md5($this->tagKeyPrefix.$tag) : $this->tagKeyPrefix.$tag;
    }

}