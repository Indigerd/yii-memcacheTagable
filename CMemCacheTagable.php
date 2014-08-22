<?php
/**
 * @author      Alexander Stepanenko <alex.stepanenko@gmail.com>
 * @license     http://mit-license.org/
 */


class CMemCacheTagable extends CMemCache
{

    public $tagKeyPrefix = 'tag_';

    public function set($id, $value, $expire = 0, $tags = null, $dependency=null)
    {
        if ($tags === null) {
            return parent::set($id, $value, $expire, $dependency);
        }
        if (is_array($tags)) {
            $tags      = array_map([$this, 'generateTagKey'], array_values($tags));
            $tagsData  = $this->getValues($tags);
            $tagsCount = count($tagsData, COUNT_RECURSIVE);
            if (empty($tagsData)) {
                $tagsData = array_fill_keys($tags, [$id]);
            } else {
                foreach ($tags as $tag) {
                    if (empty($tagsData[$tag])) {
                        $tagsData[$tag] = [$id];
                    } else if (! in_array($id, $tagsData[$tag], true)) {
                        $tagsData[$tag][] = $id;
                    }
                }
            }
            if (count($tagsData, COUNT_RECURSIVE) !== $tagsCount) {
                $this->setValues($tagsData, $expire);
            }
        } else {
            $tag      = $this->generateTagKey($tags);
            $tagData  = $this->getValue($tag);
            $tagCount = count($tagData);
            if (empty($tagData)) {
                $tagData = [$id];
            } else if (! in_array($id, $tagData, true)) {
                $tagData[] = $id;
            }
            if (count($tagData) !== $tagCount) {
                parent::set($tag, $tagData, 0);
            }
        }
        return parent::set($id, $value, $expire, $dependency);
    }

    protected function setValues($data, $expire = 0)
    {
        foreach ($data as $id => $value) parent::set($id, $value, $expire);
    }

    public function setMany(array $data, $expire = 0, $tags = null)
    {
        if ($tags === null) {
            return $this->setMany($data, $expire);
        }
        if (is_array($tags)) {
            $tags      = array_map([$this, 'generateTagKey'], array_values($tags));
            $tagsData  = $this->getValues($tags);
            $tagsCount = count($tagsData, COUNT_RECURSIVE);
            if (empty($tagsData)) {
                $tagsData = array_fill_keys($tags, array_keys($data));
            } else {
                foreach ($tags as $tag) {
                    if (empty($tagsData[$tag])) {
                        $tagsData[$tag] = array_keys($data);
                    } else {
                        $tagsData[$tag] = array_merge(
                            array_diff(array_keys($data), $tagsData[$tag]),
                            $tagsData[$tag]
                        );
                    }
                }
            }
            if (count($tagsData, COUNT_RECURSIVE) !== $tagsCount) {
                $this->setValues($tagsData, $expire);
            }
        } else {
            $tag      = $this->generateTagKey($tags);
            $tagData  = $this->getValue($tag);
            $tagCount = count($tagData);
            if (empty($tagData)) {
                $tagData = array_keys($data);
            } else {
                $tagData = array_merge(
                    array_diff(array_keys($data), $tagData),
                    $tagData
                );
            }
            if (count($tagData) !== $tagCount) {
                parent::set($tag, $tagData, 0);
            }
        }
        return $this->setValues($data, $expire);
    }

    public function getByTag($tags)
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

    protected function deleteMany(array $ids)
    {
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