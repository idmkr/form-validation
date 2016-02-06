<?php namespace Idmkr\FormValidation\Traits;

trait Writable {
    public function saveTo($path) {
        if(!is_dir($path))
            mkdir($path);

        file_put_contents(
            $path.'/'.date('Y-m-d_H-i').'.json',
            json_encode($this->data(),JSON_PRETTY_PRINT)
        );

        return $this;
    }
}