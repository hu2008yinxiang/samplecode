<?php

class Mails extends \Phalcon\Mvc\Model
{

    const SENDER_SYSTEM = '1';

    const SENDER_FBLOGIN = '2';

    const STATUS_UNREAD = 'unread';

    const STATUS_READ = 'read';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_DELETED = 'deleted';

    const STATUS_NONE = 'none';

    const TYPE_GIFT = 'gift';

    const TYPE_REQUEST = 'request';

    const TYPE_TEXT = 'text';

    const TYPE_OTHER = 'other';

    public function initialize()
    {
        $this->hasOne('src_id', 'UserAccounts', 'account_id', array(
            'alias' => 'Sender'
        ));
        $this->hasOne('dst_id', 'UserAccounts', 'account_id', array(
            'alias' => 'Receiver'
        ));
        // $this->skipAttributesOnCreate($attributes)
    }

    public function beforeValidation()
    {
        if (empty($this->content)) {
            $this->content = new \Phalcon\Db\RawValue('default');
        }
    }

    public static function newMail($sender, $receiver, $type, $content = '')
    {
        if (empty($content)) {
            $content = new \Phalcon\Db\RawValue('default');
        }
        $when = time();
        switch ($type) {
            case 'gift':
                $mail = Mails::findFirst(array(
                    'src_id = :sender: AND dst_id = :receiver: AND type = :type:',
                    'bind' => array(
                        'sender' => $sender,
                        'receiver' => $receiver,
                        'type' => $type
                    )
                ));
                if (! $mail) {
                    $mail = new Mails();
                    $mail->src_id = $sender;
                    $mail->dst_id = $receiver;
                    $mail->type = $type;
                }
                $mail->content = $content;
                $mail->when = $when;
                $mail->status = static::STATUS_UNREAD;
                $mail->save();
                break;
            case 'text':
                $mail = new Mails();
                $mail->assign(array(
                    'src_id' => $sender,
                    'dst_id' => $receiver,
                    'type' => $type,
                    'content' => $content,
                    'status' => static::STATUS_UNREAD,
                    'when' => $when
                ));
                $mail->save();
                break;
            case 'system':
                break;
        }
    }

    public static function getSenderName($id)
    {
        switch ($id) {
            case static::SENDER_SYSTEM:
                return 'System';
            case static::SENDER_FBLOGIN:
                return 'Lily';
        }
        return 'Anoymous';
    }

    public static function cleanUp($account_id)
    {
        $m = new static();
        $db = $m->getWriteConnection();
        $mm = $m->getModelsManager();
        $table = $m->getSource();
        $db->delete($table, 'when < ' . (time() - 7 * 24 * 60 * 60));
        $sum = static::sum(array(
            'dst_id = :did: AND type = :type:',
            'bind' => array(
                'did' => account_id,
                'type' => static::TYPE_TEXT
            )
        ));
        if ($sum > 30) {
            $limit = $sum - 30;
            $db->execute('DELETE FROM ' . $table . ' WHERE dst_id = ? ORDER BY mail_id ASC LIMIT ' . $limit);
        }
    }
}