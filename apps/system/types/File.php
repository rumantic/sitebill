<?php
namespace system\types;

class File {
    public $normal;
    public $preview;
    public $type;
    public $mime;
    public $size;
    public $duration;
    public $remote;

    /**
     * @return mixed
     */
    public function getNormal()
    {
        return $this->normal;
    }

    /**
     * @param mixed $normal
     */
    public function setNormal($normal)
    {
        $this->normal = $normal;
    }

    /**
     * @return mixed
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param mixed $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param mixed $mime
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getRemote()
    {
        return $this->remote;
    }

    /**
     * @param mixed $remote
     */
    public function setRemote($remote)
    {
        $this->remote = $remote;
    }
    public function toArray() {
        return json_decode(json_encode($this), true);
    }
}
