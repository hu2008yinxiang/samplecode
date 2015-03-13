<?php

class AdItems extends \Phalcon\Mvc\Model
{

    public function initialize()
    {
        $this->setSource('ad_items');
    }

    public function beforeValidationOnCreate()
    {
        if (empty($this->url) || is_null($this->url)) {
            $this->url = new \Phalcon\Db\RawValue('default');
        }
    }

    public function getRedirectUrl($from = '', $imei = '', $android_id = '')
    {
        return $this->getDI()
            ->get('url')
            ->get('redirect', array(
            'from' => $from,
            'to' => $this->package,
            'a' => $android_id,
            'type' => $this->type,
            'i' => $imei,
            'aid' => $this->aid
        ));
    }

    public function getImageUrl()
    {
        return $this->getDI()
            ->get('url')
            ->get($this->getDI()
            ->get('config')->images->baseUri . $this->image);
    }

    public function getUrl()
    {
        // TODO 应该添加 referer
        if (empty($this->url)) {
            /* return $this->getDI()
             ->get('url')
             ->get('market://details', array(
             'id' => $this->package,
             'referer' => ''
             ));*/
            return 'market://details?' . http_build_query(array(
                'id' => $this->package,
                'referer' => ''
            ));
        }
        return $this->url;
    }

    public function delete()
    {
        $img = $this->image;
        $ads = static::count(array(
            'image = :image:',
            'bind' => array(
                'image' => $img
            )
        ));
        if ($ads == 1) {
            @unlink(DATA_PATH . '/images/' . $img);
        }
        parent::delete();
    }
}
